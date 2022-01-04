<?php

namespace App\Http\Controllers;

use Carbon;
use PaymentHelper;
use Mail;
use Str;
use Validator;
use App\User;
use App\Product;
use App\Payment;
use App\PromoCode;
use App\Subscription;
use PayPal\Api\Item;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\ItemList;
use PayPal\Api\PaymentCard;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Payment as PayPalPayment;
use PayPal\Api\Transaction as PayPalTransaction;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use Illuminate\Http\Request;

require_once(base_path('vendor/chargebee/chargebee-php/lib/ChargeBee.php'));

\ChargeBee_Environment::configure(env('CHARGEBEE_SITE'), env('CHARGEBEE_KEY'));

class OrderController extends Controller
{
    private $apiContext;

    public function __construct()
    {
        $this->apiContext = new ApiContext(new OAuthTokenCredential(config('paypal.client_id'), config('paypal.secret')));
        $this->apiContext->setConfig(config('paypal.settings'));
    }

    public function cart()
    {
        $products = Product::orderBy('name')->get();
        $subscriptions = Product::subscriptions()->get();

        $cartProductIds = collect((array) auth()->user()->cart);

        $cart = collect([]);

        foreach ($cartProductIds as $productId) {
            $product = Product::find($productId);

            if (is_null($product) || $product->isSubscription() || (auth()->user()->subscribed() && $product->isSubscriptionBasic())) {
                auth()->user()->removeItemFromCart($productId);
            } else {
                $cart->push($product);
            }
        }

        $total = $cart->sum('price');

        return view('orders.cart', compact('products', 'subscriptions', 'cart', 'total'));
    }

    public function redirectToChargebee(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:monthly,annual',
        ]);

        if ($request->input('plan') === 'annual') {
            $product = Product::subscriptionYearly()->first();
        } elseif ($request->input('plan') === 'monthly') {
            $product = Product::subscriptionMonthly()->first();
        }

        $route = route('cart.add', ['product' => $product->id]);

        return view('chargebee-redirect', compact('route'));
    }

    public function payment()
    {
        session()->forget('REDIRECT_TO_CHECKOUT');

        $data['countries'] = \App\Country::orderBy('name', 'ASC')->get();

        $products = Product::all();

        $cart = auth()->user()->getCartProducts();

        $total = auth()->user()->getCartTotal();

        if ($cart->isEmpty()) {
            return redirect()->route('cart');
        }

        $cardTypes = [
            'AMEX' => 'American Express',
            'DISCOVER' => 'Discover',
            'JCB' => 'JCB',
            'MAESTRO' => 'Maestro',
            'MASTERCARD' => 'MasterCard',
            'VISA' => 'Visa',
            'ELECTRON' => 'Visa Electron',
        ];

        return view('orders.pay', compact('data', 'products', 'cart', 'total', 'cardTypes'));
    }

    public function activateFreeSubscription()
    {
        session()->flash('toastr.info', 'You already have a free subscription.');

        return redirect('/');
    }

    public function getChargebeeIframeUrl()
    {
        $validator = Validator::make(request()->all(), [
            'plan_id' => 'required|in:followouts-pro-monthly,followouts-pro-yearly',
            'is_mobile' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
            ], 422);
        }

        $planId = request()->input('plan_id');

        $data = [
            'subscription' => [
                'planId' => $planId,
                'billingCycles' => 1,
                'invoiceImmediately' => true,
                // TEMP: until we can charge future renewals we start subscription immediately
                // 'startDate' => now()->addYears(10)->timestamp,
            ],
            'embed' => 'true',
            'redirectUrl' => request()->input('is_mobile') ? route('subscribe.chargebee.handle-ajax-mobile') : route('subscribe.chargebee.handle-ajax'),
            'cancelUrl' => route('university'),
        ];

        // Add setup fee
        $data['subscription']['addons'][0]['id'] = config('followouts.chargebee_setup_fee');

        $firstMonthFree = $planId === 'followouts-pro-monthly';

        if ($firstMonthFree) {
            $data['subscription']['coupon'] = config('followouts.chargebee_monthly_promo_coupon');
        }

        $result = \Chargebee_HostedPage::CheckoutNew($data);

        $hostedPageUrl = $result->hostedPage()->url;

        return response()->json(['status' => 'OK', 'url' => $hostedPageUrl]);
    }

    public function handleChargebeeSubscribeViaAjaxForMobileApp(Request $request)
    {
        // Return JSON response
        return $this->handleChargebeeSubscribeViaAjax($request, true);
    }

    public function handleChargebeeSubscribeViaAjax(Request $request, $forMobileApp = false)
    {
        $hostedPageId = $request->input('id');
        $status = $request->input('state');

        try {
            if ($status == 'succeeded') {
                try {
                    $result = \ChargeBee_HostedPage::acknowledge($hostedPageId);
                    $hostedPage = $result->hostedPage();
                    $content = $hostedPage->content();

                    $subscription = PaymentHelper::parseChargebeeSubscriptionFromResponse($content->subscription());
                    $customer = PaymentHelper::parseChargebeeCustomerFromResponse($content->customer());

                    // TEMP: until we can charge future renewals we start subscription immediately
                    // $subscriptionCode = PaymentHelper::createChargebeeSubscriptionCode($subscription['id'], $customer['email'], $subscription['plan_id']);
                    $subscriptionCode = PaymentHelper::createChargebeeSubscriptionCodeAndStartSubscription($subscription['id'], $customer['email'], $subscription['plan_id']);

                    if (is_null($subscriptionCode)) {
                        if ($forMobileApp) {
                            return response()->json([
                                'status' => 'error',
                                'message' => 'Payment failed, please try again.',
                            ], 400);
                        }

                        session()->flash('toastr.error', 'Payment failed, please try again.');

                        return redirect()->route('university');
                    }

                    Mail::to($customer['email'])->send(new \App\Mail\SubscriptionCodePurchased($subscriptionCode));
                } catch (\ChargeBee_InvalidRequestException $e) {
                    if ($e->getApiErrorCode() == 'invalid_state_for_request') {
                        return abort(400, 'Invalid state for request.');
                    } else {
                        throw $e;
                    }
                }
            } else {
                return abort(400, 'Invalid state for request.');
            }
        } catch (Exception $e) {
            throw $e;
        }

        if ($forMobileApp) {
            return response()->json([
                'status' => 'OK',
                'code' => new \App\Http\Resources\SubscriptionCodeResource($subscriptionCode),
            ]);
        }

        return view('subscription-code-receipt', compact('subscriptionCode'));
    }

    public function subscribeViaChargeBee()
    {
        session()->forget('REDIRECT_TO_CHARGEBEE');

        $product = (auth()->user()->getCartProducts())->first();

        $planId = $product->isSubscriptionYearly() ? 'followouts-pro-yearly' : 'followouts-pro-monthly';

        $subscription = PaymentHelper::getChargebeeSubscription(auth()->user()->id);

        if ($subscription !== null) {
            session()->flash('toastr.success', 'You\'re already subscribed.');

            return redirect()->route('settings.payments');
        } else {
            $data = [
                'subscription' => [
                    'planId' => $planId,
                ],
                'customer' => [
                    'id' => auth()->user()->id,
                    'email' => auth()->user()->email,
                ],
                'embed' => 'false',
                'redirectUrl' => url('/chargebee/handle'),
                'cancelUrl' => url('/'),
            ];

            $firstMonthFree = $planId === 'followouts-pro-monthly' && !auth()->user()->wasSubscribedTo($planId);
            $firstYearFree = $planId === 'followouts-pro-yearly' && !auth()->user()->wasSubscribedTo($planId) && auth()->user()->wasInvitedBySalesRepWithPromo();

            if ($firstMonthFree) {
                $data['subscription']['coupon'] = config('followouts.chargebee_monthly_promo_coupon');
            } elseif ($firstYearFree) {
                $data['subscription']['coupon'] = config('followouts.chargebee_annual_promo_coupon');
            }

            if (!auth()->user()->wasSubscribedTo($planId)) {
                $data['subscription']['addons'][0]['id'] = config('followouts.chargebee_setup_fee');
            }

            $result = \Chargebee_HostedPage::CheckoutNew($data);
        }

        $hostedPageUrl = $result->hostedPage()->url;

        return redirect($hostedPageUrl);
    }

    public function handleSubscribeViaChargeBee(Request $request)
    {
        $hostedPageId = $request->input('id');
        $status = $request->input('state');

        try {
            if ($status == 'succeeded') {
                try {
                    $result = \ChargeBee_HostedPage::acknowledge($hostedPageId);
                    $hostedPage = $result->hostedPage();

                    $content = $hostedPage->content();

                    $subscription = PaymentHelper::parseChargebeeSubscriptionFromResponse($content->subscription());

                    $user = User::find($subscription['customer_id']);

                    $payment = new Payment;
                    $payment->amount = number_format($subscription['plan_unit_price'] / 100, 2);
                    $payment->payment_method = 'chargebee';

                    $products = [];

                    $product = $subscription['plan_id'] === 'followouts-pro-yearly' ? Product::subscriptionYearly()->first() : Product::subscriptionMonthly()->first();

                    $firstMonthFree = $subscription['plan_id'] === 'followouts-pro-monthly' && !$user->wasSubscribedTo($subscription['plan_id']);
                    $firstYearFree = $subscription['plan_id'] === 'followouts-pro-yearly' && !$user->wasSubscribedTo($subscription['plan_id']) && $user->wasInvitedBySalesRepWithPromo();

                    $productPrice = (float) number_format($subscription['plan_unit_price'] / 100, 2);

                    if ($firstMonthFree || $firstYearFree) {
                        // Set subscription price to $0.00
                        $productPrice = (float) number_format(0, 2);
                        // Set total price to $0.00
                        $payment->amount = number_format(0, 2);
                    }

                    array_push($products, [
                        'name' => $product->name,
                        'description' => $product->description,
                        'type' => $product->type,
                        'price' => $productPrice,
                    ]);

                    // If it's the first time user subscribes to this plan we'll charge setup fee
                    if (!$user->wasSubscribedTo($subscription['plan_id'])) {
                        $product = Product::subscriptionSetupFee()->first();

                        // Update total amount
                        $payment->amount = number_format((float) $payment->amount + (float) $product->price, 2);

                        array_push($products, [
                            'name' => $product->name,
                            'description' => $product->description,
                            'type' => $product->type,
                            'price' => (float) number_format($product->price, 2),
                        ]);
                    }

                    $payment->products = $products;
                    $payment->payment_id = 'CHARGEBEE-' . mb_strtoupper(Str::random(24));
                } catch (\ChargeBee_InvalidRequestException $e) {
                    if ($e->getApiErrorCode() == 'invalid_state_for_request') {
                        return abort(400, 'Invalid state for request');
                    } else {
                        throw $e;
                    }
                }
            } else {
                return abort(400, 'Invalid state for request.');
            }
        } catch (Exception $e) {
            throw $e;
        }

        $payment = $user->payments()->save($payment);

        PaymentHelper::updateOrCreateChargebeeSubscription($user->id, $subscription);

        $user->notify(new \App\Notifications\PaymentCompleted($payment));

        return redirect()->route('payments.show', ['payment' => $payment->id]);
    }

    public function pay(Request $request)
    {
        $user = auth()->user();

        $cart = $user->getCartProducts();

        $amount = $user->getCartTotal();

        $promoCode = $request->input('promo_code', null);

        if ($cart->isEmpty()) {
            return redirect()->route('cart');
        }

        if ($promoCode) {
            if (PaymentHelper::validatePromoCode($promoCode, $user)) {
                $promoCode = PromoCode::where('code', $request->input('promo_code'))->first();
                $amount = $amount - $promoCode->amount;

                $amount = $amount > 0 ? $amount : 0;
            } else {
                session()->flash('toastr.error', 'Promo code is invalid.');

                return redirect()->route('cart');
            }
        }

        // Save payment
        $payment = new Payment;
        $payment->amount = $amount;

        if ($amount > 0) {
            $this->validate($request, [
                'card_number' => 'required|string|max:30',
                'card_cvv' => 'required|numeric|digits_between:3,4',
                'card_type' => 'required|in:AMEX,DISCOVER,JCB,MAESTRO,MASTERCARD,VISA,ELECTRON',
                'expires_on_month' => 'required|date_format:m',
                'expires_on_year' => 'required|date_format:Y|after_or_equal:now',
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'country_code' => 'required|exists:countries,code',
            ]);

            $cardNumber = str_replace(' ', '', $request->input('card_number'));
            $expiresMonth = strlen($request->input('expires_on_month')) === 1 ? '0' . $request->input('expires_on_month') : $request->input('expires_on_month');
            $invoiceNumber = uniqid();

            $card = new PaymentCard;
            $card->setType($request->input('card_type'))
                 ->setNumber($cardNumber)
                 ->setExpireMonth($expiresMonth)
                 ->setExpireYear($request->input('expires_on_year'))
                 ->setCvv2($request->input('card_cvv'))
                 ->setFirstName($request->input('first_name'))
                 ->setBillingCountry($request->input('country_code'))
                 ->setLastName($request->input('last_name'));

            $fi = new FundingInstrument;
            $fi->setPaymentCard($card);

            $payer = new Payer;
            $payer->setPaymentMethod('credit_card')->setFundingInstruments([$fi]);

            $orderAmount = new Amount;
            $orderAmount->setCurrency('USD')->setTotal($amount);

            $transaction = new PayPalTransaction;
            $transaction->setAmount($orderAmount)->setDescription('Payment to FollowOut LLC.')->setInvoiceNumber($invoiceNumber);

            $paypalPayment = new PayPalPayment;
            $paypalPayment->setIntent('sale')->setPayer($payer)->setTransactions([$transaction]);

            try {
                $paypalPayment->create($this->apiContext);
            } catch (\Exception $e) {
                session()->flash('toastr.error', 'Payment failed. Please try again.');
                return redirect()->back();
            }

            if ($paypalPayment->getState() !== 'approved') {
                session()->flash('toastr.error', 'Payment failed. Please contact our support team.');
                return redirect()->back();
            }

            $payment->payment_method = 'credit_card';
            $payment->payment_id = $paypalPayment->getId();
        } else {
            $payment->payment_method = 'promo_code';
            $payment->payment_id = 'PROMOCODE-' . mb_strtoupper(Str::random(24));
        }

        if ($promoCode) {
            $payment->promo_code = $promoCode->code;
            $payment->promo_code_amount = $promoCode->amount;

            $promoCode->users()->attach($user->id);
            $promoCode->save();
        }

        $products = [];

        foreach ($cart as $product) {
            array_push($products, [
                'name' => $product->name,
                'description' => $product->description,
                'type' => $product->type,
                'price' => (float) $product->price,
            ]);
        }

        $payment->products = $products;
        $payment = $user->payments()->save($payment);

        if ($cart->where('type', 'geo_coupon')->count() > 0) {
            $coupons = $cart->whereStrict('type', 'geo_coupon')->all();

            // TODO: attach coupons to Followout
        }

        if ($cart->where('type', 'subscription_basic')->count() > 0) {
            PaymentHelper::updateOrCreateSubscription($user->id, 'subscription_basic');
        }

        $user->clearCart();

        $user->notify(new \App\Notifications\PaymentCompleted($payment));

        return redirect()->route('payments.show', ['payment' => $payment->id]);
    }

    public function addProduct(Product $product)
    {
        $user = auth()->user();

        if ($product->isSubscriptionBasic() || $product->isSubscription()) {
            if (!$user->isFollowhost()) {
                session()->flash('toastr.info', 'Register as business to purchase ' . $product->name);
                return redirect()->back();
            }
        }

        if ($product->isSubscription()) {
            if ($user->subscribedToPro()) {
                session()->flash('toastr.info', 'You already have a Pro subscription.');
                return redirect()->back();
            }

            $user->clearCart();
            $cart = collect([]);
            $cart->push($product->id);
            $user->cart = $cart->toArray();
            $user->save();

            return redirect()->route('subscribe.chargebee');
        }

        if ($product->isSubscriptionBasic()) {
            // Basic (Freelancers) subscription is hidden now
            $user->clearCart();
            session()->flash('toastr.info', 'Basic subscription is not available.');
            return redirect()->back();

            if ($user->subscribedToPro()) {
                session()->flash('toastr.info', 'You already have a Pro subscription.');
                return redirect()->back();
            }

            if ($user->subscribed() && !$user->subscribedToPro()) {
                session()->flash('toastr.info', 'You already have a Basic subscription.');
                return redirect()->back();
            }

            if ($user->getCartProducts()->where('type', 'subscription_basic')->count() > 0) {
                session()->flash('toastr.info', $product->name.' is already in your cart.');
                return redirect()->back();
            }
        }

        $cart = collect((array) $user->cart);

        $cart->push($product->id);
        $cart = $cart->sort();

        $user->cart = $cart->toArray();
        $user->save();

        session()->flash('toastr.success', $product->name.' has been added.');

        return redirect()->route('cart');
    }

    public function removeItem($id)
    {
        auth()->user()->removeItemFromCart($id);

        session()->flash('toastr.success', 'Item has been deleted.');

        return redirect()->route('cart');
    }
}

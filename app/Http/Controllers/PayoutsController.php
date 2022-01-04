<?php

namespace App\Http\Controllers;

use App\User;
use App\Payout;
use App\Product;
use PayPal\Api\Currency as PayPalCurrency;
use PayPal\Api\Payout as PayPalPayout;
use PayPal\Api\PayoutItem as PayPalPayoutItem;
use PayPal\Api\PayoutSenderBatchHeader;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Illuminate\Http\Request;

class PayoutsController extends Controller
{
    private $apiContext;

    public function __construct()
    {
        $this->middleware('role:admin');

        $this->apiContext = new ApiContext(new OAuthTokenCredential(config('paypal.client_id'), config('paypal.secret')));
        $this->apiContext->setConfig(config('paypal.settings'));
    }

    public function index()
    {
        $payouts = Payout::orderBy('created_at', 'desc')->paginate(25);

        return view('payouts.index', compact('payouts'));
    }

    public function create()
    {
        $itemTypes = Product::services()->get();

        return view('payouts.create', compact('itemTypes'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'recipient' => 'required|email',
            'item_type' => 'required|in:followee_services,other',
            'amount' => 'required|regex:/^\d*(\.\d{2})?$/',
            'notes' => 'nullable|string|max:250',
        ]);

        $recipient = mb_strtolower($request->input('recipient'));
        $itemType = $request->input('item_type');
        $amount = number_format((float) $request->input('amount'), 2, '.', '');

        if ($amount <= 0) {
            session()->flash('toastr.error', 'Amount should be bigger than 0.');
            return redirect()->back()->withInput();
        }

        $payout = new Payout;
        $payout->recipient = $recipient;
        $payout->amount = $amount;
        $payout->notes = $request->input('notes', null);
        $payout->item_type = $itemType;
        $payout->save();

        $user = User::where('email', $recipient)->first();

        if ($user) {
            $payout->user()->associate($user);
            $payout->save();
        }

        session()->flash('toastr.success', 'Payout created successfully.');

        return redirect()->route('payouts.index');
    }

    public function show(Payout $payout)
    {
        $result = $payout->getPayoutData();

        if ($result) {
            return dd($result); // return response()->json($result);
        }

        session()->flash('toastr.error', 'Payout data cannot be loaded.');

        return redirect()->route('payouts.index');
    }

    public function approve(Payout $payout)
    {
        if ($payout->isPending() || $payout->isCompleted()) {
            return abort(404);
        }

        $paypalPayout = new PayPalPayout;

        $senderBatchHeader = new PayoutSenderBatchHeader;
        $senderBatchHeader->setSenderBatchId(uniqid())->setEmailSubject('You have a payment');

        $paypalAmount = new PayPalCurrency;
        $paypalAmount->setCurrency('USD');
        $paypalAmount->setValue($payout->amount);

        $senderItem = new PayPalPayoutItem;
        $senderItem->setRecipientType('EMAIL')
            ->setReceiver($payout->recipient)
            ->setSenderItemId($payout->item_type)
            ->setAmount($paypalAmount);

        $paypalPayout->setSenderBatchHeader($senderBatchHeader)->addItem($senderItem);

        try {
            $result = $paypalPayout->create(null, $this->apiContext);
        } catch (Exception $e) {
            session()->flash('toastr.error', 'Payout creation failed. Please try again.');
            return redirect()->back();
        }

        $payout->batch_id = $result->getBatchHeader()->getPayoutBatchId();
        $payout->batch_status = $result->getBatchHeader()->getBatchStatus();
        $payout->save();

        session()->flash('toastr.success', 'Payout was successfully sent.');

        return redirect()->route('payouts.index');
    }

    public function cancel(Payout $payout)
    {
        if ($payout->isPending() || $payout->isCompleted()) {
            return abort(404);
        }

        $payout->delete();

        session()->flash('toastr.success', 'Payout was canceled.');

        return redirect()->route('payouts.index');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use Carbon;
use Hash;
use Str;
use App\User;
use App\Country;
use App\Product;
use App\FollowoutCategory;
use App\SalesRepresentative;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Rules\NoFollowoutWordInString;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use PaymentHelper;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RedirectsUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::PROFILE;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except(['showSocialRegistrationForm', 'registerFromSocial']);
        $this->middleware('auth')->only(['showSocialRegistrationForm', 'registerFromSocial']);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        event(new Registered($user));

        $user->sendAccountActivationEmail();

        auth()->guard()->login($user);

        return redirect($this->redirectPath());
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm(Request $request)
    {
        $data['plan'] = $request->input('plan', 'free');
        $data['followout_categories'] = FollowoutCategory::orderBy('name', 'ASC')->get();
        $data['countries'] = Country::orderBy('name', 'ASC')->get();
        $data['countries_us'] = Country::getUS();

        if ($data['plan'] !== 'free') {
            return view('auth.register-and–pay', compact('data'));
        }

        return view('auth.register', compact('data'));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        if (isset($data['plan']) && $data['plan'] !== 'free') {
            $rules = [
                'name' => ['required', 'string', 'max:128', new NoFollowoutWordInString],
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'is_followhost' => 'accepted',
                'terms' => 'accepted',
                // 'plan' => 'required|in:basic,monthly,annual',
                'plan' => 'required|in:monthly,annual',
                'sales_rep' => 'nullable|string|sales_rep_code_exists',
            ];
        } else {
            $rules = [
                'name' => ['required', 'string', 'max:128', new NoFollowoutWordInString],
                'email' => 'required|string|email|max:255|unique:users,email',
                'phone_number' => 'nullable|phone_number|unique:users,phone_number',
                'password' => 'required|string|min:8|confirmed',
                'is_followhost' => 'nullable',
                'gender' => 'nullable|in:male,female',
                'birthday' => 'nullable|date_format:'.config('followouts.date_format').'|before:-16 years|after:-100 years',
                'account_categories' => 'required|array|max:5',
                'account_categories.*' => 'required|string|distinct|exists:followout_categories,_id',
                'lat' => 'required_with:is_followhost|nullable|lat|not_in:0',
                'lng' => 'required_with:is_followhost|nullable|lng|not_in:0',
                'country_id' => 'required_with:is_followhost|nullable|exists:countries,_id',
                'state' => 'nullable|string|max:100',
                'city' => 'required_with:is_followhost|nullable|string|max:100',
                'address' => 'required_with:is_followhost|nullable|string|max:100',
                'zip_code' => 'required|string|min:5|max:12',
                'website' => 'nullable|url',
                'education' => 'nullable|string|max:100',
                'about' => 'nullable|string|max:2500',
                'terms' => 'accepted',
                // 'plan' => 'nullable|in:free,basic,monthly,annual',
                'plan' => 'nullable|in:free,monthly,annual',
                'sales_rep' => 'nullable|string|sales_rep_code_exists',
                'autosubcribe_to_followhosts' => 'nullable',
                'available_for_promotion' => 'nullable',
                'subscription_code' => 'nullable|exists:subscription_codes,code',
            ];
        }

        return Validator::make($data, $rules);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone_number = isset($data['phone_number']) && $data['phone_number'] ? $data['phone_number'] : null;
        $user->password = Hash::make($data['password']);
        $user->is_activated = false;
        $user->gender = isset($data['gender']) && $data['gender'] ? $data['gender'] : null;
        $user->birthday = isset($data['birthday']) && $data['birthday'] ? Carbon::createFromFormat(config('followouts.date_format'), $data['birthday']) : null;
        $user->address = isset($data['address']) && $data['address'] ? $data['address'] : null;
        $user->state = isset($data['state']) && $data['state'] ? $data['state'] : null;
        $user->city = isset($data['city']) && $data['city'] ? $data['city'] : null;
        $user->zip_code =  isset($data['zip_code']) && $data['zip_code'] ? $data['zip_code'] : null;
        $user->website = isset($data['website']) && $data['website'] ? $data['website'] : null;
        $user->education = isset($data['education']) && $data['education'] ? $data['education'] : null;
        $user->about = isset($data['about']) && $data['about'] ? $data['about'] : null;
        $user->keywords = null;
        $user->api_token = Str::random(100);
        $user->last_seen = now();
        $user->role = 'friend';
        $user->privacy_type = 'private';
        $user->video_url = '';

        if (isset($data['is_followhost']) && $data['is_followhost']) {
            $user->role = 'followhost';

            if (isset($data['lat']) && isset($data['lng'])) {
                $user->lat = doubleval($data['lat']);
                $user->lng = doubleval($data['lng']);
            }

            if (isset($data['plan'])) {
                // if ($data['plan'] === 'basic') {
                //     session()->put('REDIRECT_TO_CHECKOUT', $data['plan']);
                // } else {
                //     session()->put('REDIRECT_TO_CHARGEBEE', $data['plan']);
                // }

                session()->put('REDIRECT_TO_CHARGEBEE', $data['plan']);
            }
        } else {
            $user->autosubcribe_to_followhosts = !empty($data['autosubcribe_to_followhosts']);
        }

        $user->available_for_promotion = !empty($data['available_for_promotion']);
        $user->save();

        if (isset($data['country_id']) && $data['country_id']) {
            $user->country()->associate(Country::find($data['country_id']));
        }

        if (isset($data['plan']) && $data['plan'] !== 'free') {
            $user->account_categories()->attach([ FollowoutCategory::getSocialCategory()->id ]);
        } else {
            $user->account_categories()->attach($data['account_categories']);
        }

        $user->save();

        if (isset($data['sales_rep']) && $data['sales_rep']) {
            $salesRep = SalesRepresentative::where('code', $data['sales_rep'])->orWhere('promo_code', $data['sales_rep'])->first();

            $viaPromoCode = false;

            if ($data['sales_rep'] === $salesRep->promo_code) {
                $viaPromoCode = true;
            }

            $salesRep->addReferredUser($user->id, $viaPromoCode);
        }

        if (isset($data['subscription_code'])) {
            $subscriptionCode = PaymentHelper::useChargebeeSubscriptionCode($data['subscription_code'], $user->id);

            $accountActivationToken = $data['account_activation_token'] ?? null;

            if ($subscriptionCode) {
                session()->flash('toastr.success', 'Subscription has been activated successfully.');

                // If user clicked a link from subscription code email that was sent to his email then we make his email validated
                if ($user->email === $subscriptionCode->email && $subscriptionCode->account_activation_token === $accountActivationToken) {
                    $user->is_activated = true;
                    $user->save();
                }

                $user->load('subscription');
            }
        }

        return $user;
    }

    public function showSocialRegistrationForm(Request $request)
    {
        $data['followout_categories'] = FollowoutCategory::orderBy('name', 'ASC')->get();
        $data['countries'] = Country::orderBy('name', 'ASC')->get();
        $data['countries_us'] = Country::getUS();

        return view('auth.register-social', compact('data'));
    }

    public function registerFromSocial(Request $request)
    {
        $user = auth()->user();

        if ($user->isRegistered()) {
            return abort(403, 'Access denied.');
        }

        $emailRequirement = $user->email ? 'nullable' : 'required';

        $this->validate($request, [
            'name' => 'required|string|max:128',
            'email' => $emailRequirement . '|string|email|max:255|unique:users,email,' . $user->id . ',_id',
            'phone_number' => 'nullable|phone_number|unique:users,phone_number',
            'password' => 'required|string|min:8|confirmed',
            'is_followhost' => 'nullable',
            'gender' => 'nullable|in:male,female',
            'birthday' => 'nullable|date_format:'.config('followouts.date_format').'|before:-16 years|after:-100 years',
            'account_categories' => 'required|array|max:5',
            'account_categories.*' => 'required|string|distinct|exists:followout_categories,_id',
            'lat' => 'required_with:is_followhost|nullable|lat|not_in:0',
            'lng' => 'required_with:is_followhost|nullable|lng|not_in:0',
            'country_id' => 'required_with:is_followhost|nullable|exists:countries,_id',
            'state' => 'nullable|string|max:100',
            'city' => 'required_with:is_followhost|nullable|string|max:100',
            'address' => 'required_with:is_followhost|nullable|string|max:100',
            'zip_code' => 'required|string|min:5|max:12',
            'website' => 'nullable|url',
            'education' => 'nullable|string|max:100',
            'about' => 'nullable|string|max:2500',
            'terms' => 'accepted',
            'sales_rep' => 'nullable|string|sales_rep_code_exists',
        ]);

        $user->name = $request->input('name');

        if (is_null($user->email)) {
            $user->email = $request->input('email');
            $shouldSendActivationEmail = true;
        }

        $user->phone_number = $request->input('phone_number', null);
        $user->password = bcrypt($request->input('password'));
        $user->gender = $request->input('gender', null);
        $user->birthday = $request->input('birthday', null) ? Carbon::createFromFormat(config('followouts.date_format'), $request->input('birthday')) : null;
        $user->address = $request->input('address', null);
        $user->city = $request->input('city', null);
        $user->state = $request->input('state', null);
        $user->zip_code = $request->input('zip_code');
        $user->website = $request->input('website', null);
        $user->education = $request->input('education', null);
        $user->about = $request->input('about', null);
        $user->keywords = null;
        $user->is_unregistered = false;

        if ($request->input('is_followhost', false)) {
            $user->role = 'followhost';
            $user->lat = doubleval($request->input('lat'));
            $user->lng = doubleval($request->input('lng'));
        }

        $user->save();

        if ($request->input('country_id', null)) {
            $user->country()->associate(Country::find($request->input('country_id')));
        }

        $user->account_categories()->attach($request->input('account_categories'));
        $user->save();

        if ($request->input('sales_rep', null)) {
            $salesRep = SalesRepresentative::where('code', $request->input('sales_rep'))->orWhere('promo_code', $request->input('sales_rep'))->first();

            $viaPromoCode = false;

            if ($request->input('sales_rep') === $salesRep->promo_code) {
                $viaPromoCode = true;
            }

            $salesRep->addReferredUser($user->id, $viaPromoCode);
        }

        if (isset($shouldSendActivationEmail)) {
            $user->sendAccountActivationEmail();
        }

        return redirect($this->redirectPath());
    }
}

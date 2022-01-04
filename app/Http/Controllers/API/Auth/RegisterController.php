<?php

namespace App\Http\Controllers\API\Auth;

use Carbon;
use Hash;
use Validator;
use Str;
use App\User;
use App\Country;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use PaymentHelper;

class RegisterController extends Controller
{
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $this->create($request->all());

        event(new Registered($user));

        $user->sendAccountActivationEmail();

        return response()->json([
            'status' => 'OK',
            'user' => $user,
            'api_token' => $user->api_token,
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:128',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone_number' => 'nullable|phone_number|unique:users,phone_number',
            'password' => 'required|string|min:8',
            'is_followhost' => 'nullable',
            'gender' => 'nullable|in:male,female',
            'birthday' => 'nullable|date_format:'.config('followouts.date_format').'|before:-16 years|after:-100 years',
            'account_categories' => 'required|array|max:5',
            'account_categories.*' => 'required|string|distinct|exists:followout_categories,_id',
            'lat' => 'required_with:is_followhost|nullable|lat|not_in:0',
            'lng' => 'required_with:is_followhost|nullable|lng|not_in:0',
            'country_id' => 'required_with:is_followhost|nullable|exists:countries,_id',
            'city' => 'required_with:is_followhost|nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'address' => 'required_with:is_followhost|nullable|string|max:100',
            'zip_code' => 'required|string|min:5|max:12',
            'website' => 'nullable|url',
            'education' => 'nullable|string|max:100',
            'about' => 'nullable|string|max:2500',
            'subscription_code' => 'nullable|exists:subscription_codes,code',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone_number = isset($data['phone_number']) ? $data['phone_number'] : null;
        $user->password = Hash::make($data['password']);
        $user->is_activated = false;
        $user->gender = isset($data['gender']) ? $data['gender'] : null;
        $user->birthday = isset($data['birthday']) ? Carbon::createFromFormat(config('followouts.date_format'), $data['birthday']) : null;
        $user->address = isset($data['address']) ? $data['address'] : null;
        $user->city = isset($data['city']) ? $data['city'] : null;
        $user->state = isset($data['state']) ? $data['state'] : null;
        $user->zip_code = $data['zip_code'];
        $user->website = isset($data['website']) ? $data['website'] : null;
        $user->education = isset($data['education']) ? $data['education'] : null;
        $user->about = isset($data['about']) ? $data['about'] : null;
        $user->api_token = Str::random(100);
        $user->last_seen = now();
        $user->role = 'friend';
        $user->privacy_type = 'private';

        if (isset($data['is_followhost'])) {
            $user->role = 'followhost';
            $user->lat = isset($data['lat']) ? doubleval($data['lat']) : 0;
            $user->lng = isset($data['lng']) ? doubleval($data['lng']) : 0;
        } else {
            $user->autosubcribe_to_followhosts = isset($data['autosubcribe_to_followhosts']) ? (bool) $data['autosubcribe_to_followhosts'] : false;
            $user->available_for_promotion = isset($data['available_for_promotion']) ? (bool) $data['available_for_promotion'] : false;
        }

        $user->save();

        if (isset($data['country_id'])) {
            $user->country()->associate(Country::find($data['country_id']));
        }

        $user->account_categories()->attach($data['account_categories']);
        $user->save();

        if (isset($data['subscription_code'])) {
            $subscriptionCode = PaymentHelper::useChargebeeSubscriptionCode($data['subscription_code'], $user->id);

            $accountActivationToken = $data['account_activation_token'] ?? null;

            if ($subscriptionCode) {
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

    public function registerFromSocial(Request $request)
    {
        $user = auth()->guard('api')->user();

        if ($user->isRegistered()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can\'t register again.'
            ], 403);
        }

        $emailRequirement = $user->email ? 'nullable' : 'required';

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:128',
            'email' => $emailRequirement.'|string|email|max:255|unique:users,email',
            'phone_number' => 'nullable|phone_number|unique:users,phone_number',
            'password' => 'nullable|string|min:8',
            'is_followhost' => 'nullable',
            'gender' => 'nullable|in:male,female',
            'birthday' => 'nullable|date_format:'.config('followouts.date_format').'|before:-16 years|after:-100 years',
            'account_categories' => 'required|array|max:5',
            'account_categories.*' => 'required|string|distinct|exists:followout_categories,_id',
            'lat' => 'required_with:is_followhost|nullable|lat|not_in:0',
            'lng' => 'required_with:is_followhost|nullable|lng|not_in:0',
            'country_id' => 'required_with:is_followhost|nullable|exists:countries,_id',
            'city' => 'required_with:is_followhost|nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'address' => 'required_with:is_followhost|nullable|string|max:100',
            'zip_code' => 'required|string|min:5|max:12',
            'website' => 'nullable|url',
            'education' => 'nullable|string|max:100',
            'about' => 'nullable|string|max:2500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user->name = $request->input('name');

        if (is_null($user->email)) {
            $user->email = $request->input('email');
            $shouldSendActivationEmail = true;
        }

        $user->phone_number = $request->input('phone_number');

        if ($request->input('password', null)) {
            $user->password = bcrypt($request->input('password'));
        }

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
        } else {
           $user->autosubcribe_to_followhosts = $request->input('autosubcribe_to_followhosts', null) ? true : false;
        }

        $user->available_for_promotion = $request->input('available_for_promotion', null) ? true : false;
        $user->save();

        if ($request->input('country_id')) {
            $user->country()->associate(Country::find($request->input('country_id')));
        }

        $user->account_categories()->attach($request->input('account_categories'));
        $user->save();

        if (isset($shouldSendActivationEmail)) {
            $user->sendAccountActivationEmail();
        }

        return response()->json([
            'status' => 'OK',
            'user' => $user,
            'api_token' => $user->api_token,
        ]);
    }
}

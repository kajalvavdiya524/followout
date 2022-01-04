<?php

namespace App\Http\Controllers\API\Auth;

use Validator;
use SocialHelper;
use Str;
use App\User;
use App\Country;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ThrottlesLogins;

class LoginController extends Controller
{
    use ThrottlesLogins;

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

    /**
    * Handle a login request to the application.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
    */
   public function login(Request $request)
   {
       $this->validateLogin($request);

       // If the class is using the ThrottlesLogins trait, we can automatically throttle
       // the login attempts for this application. We'll key this by the username and
       // the IP address of the client making these requests into this application.
       if ($this->hasTooManyLoginAttempts($request)) {
           $this->fireLockoutEvent($request);
           return $this->sendLockoutResponse($request);
       }

       if ($this->attemptLogin($request)) {
           return $this->sendLoginResponse($request);
       }

       // If the login attempt was unsuccessful we will increment the number of attempts
       // to login and redirect the user back to the login form. Of course, when this
       // user surpasses their maximum number of attempts they will get locked out.
       $this->incrementLoginAttempts($request);

       return $this->sendFailedLoginResponse($request);
   }

    /**
    * Validate the user login request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return void
    */
    protected function validateLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            $this->username() => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendFailedLoginResponse($request);
        }

        return true;
    }

    /**
    * Attempt to log the user into the application.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return bool
    */
    protected function attemptLogin(Request $request)
    {
        return auth()->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $data = $request->only($this->username(), 'password');

        if (isset($data[$this->username()])) {
            $data[$this->username()] = mb_strtolower($data[$this->username()]);
        }

        return $data;
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        $user = User::where($this->username(), $request->input($this->username()))->first();
        $user = new UserResource(User::with(User::$withAll)->find($user->id));

        return response()->json([
            'status' => 'OK',
            'user' => $user,
            'api_token' => $user->api_token
        ]);
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $errors = [$this->username() => trans('auth.failed')];

        return response()->json([
            'status' => 'error',
            'errors' => $errors
        ], 422);
    }

    public function handleFacebookLogin(Request $request)
    {
        $token = $request->input('token');

        $validator = Validator::make($request->all(), [
            'token' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = SocialHelper::createOrGetUserFromToken($request->input('token'), 'facebook');

        if ($user) {
            $user = new UserResource(User::with(User::$withAll)->find($user->id));

            return response()->json([
                'status' => 'OK',
                'user' => $user,
                'api_token' => $user->api_token
            ]);
        }

        return $this->sendFailedLoginResponse();
    }

    public function loginAnonymous(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uuid' => ['required', 'uuid'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!User::where('anonymous_user_id', $request->input('uuid'))->exists()) {
            // No email and password can be set for anonymous users
            $user = new User;
            $user->name = 'Anonymous User';
            $user->is_unregistered = true;
            $user->is_activated = false;
            $user->role = 'friend';
            $user->privacy_type = 'private';
            $user->api_token = Str::random(100);
            $user->last_seen = now();
            $user->anonymous_user_id = $request->input('uuid');
            $user->save();
        }

        $user = new UserResource(User::with(User::$withAll)->where('anonymous_user_id', $request->input('uuid'))->first());

        return response()->json([
            'status' => 'OK',
            'user' => $user,
            'api_token' => $user->api_token
        ]);
    }
}

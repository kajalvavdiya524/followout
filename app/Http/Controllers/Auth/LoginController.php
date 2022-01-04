<?php

namespace App\Http\Controllers\Auth;

use Socialite;
use SocialHelper;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except(['redirectToFacebookProvider', 'handleFacebookProviderCallback', 'logout']);
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

        $data[$this->username()] = mb_strtolower($data[$this->username()]);

        return $data;
    }

    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return Response
     */
    public function redirectToFacebookProvider()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->isActivated() && $user->isRegistered() && !$user->hasAvatar()) {
            session()->flash('SHOW_PROFILE_PICTURE_TUTORIAL', true);

            return redirect()->intended(route('users.edit', ['user' => $user->id]));
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Obtain the user information from Facebook.
     *
     * @return Response
     */
    public function handleFacebookProviderCallback()
    {
        try {
            $providerUser = Socialite::driver('facebook')->user();
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw new \Exception('Oops, Facebook API is offline. Please try again.', 500);
        }

        $user = SocialHelper::createOrGetUserFromToken($providerUser->token, 'facebook');

        if (auth()->check()) {
            // When user tries to connect account from settings page
            // reassign social account to auth user if account was previously connected to a different user
            // if (auth()->user()->id !== $user->id) {
            //     SocialHelper::reassignSocialAccount($providerUser, 'facebook', auth()->user());
            // }

            session()->flash('toastr.success', 'Facebook has been successfully connected.');

            return redirect()->route('settings.account');
        }

        if ($user) {
            auth()->login($user);
        }

        return redirect($this->redirectTo);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use Str;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset', compact('token'));
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        $this->validate($request,[
            'token' => 'required|exists:users,password_reset_token',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = User::where('email', $request->input('email'))
                    ->where('password_reset_token', $request->input('token'))
                    ->where('password_reset_token_expires_at', '>=', now())
                    ->firstOrFail();

        $user->password = bcrypt($request->input('password'));
        $user->remember_token = Str::random(60);
        $user->password_reset_token = null;
        $user->password_reset_token_expires_at = null;
        $user->save();

        auth()->login($user);

        session()->flash('toastr.success', 'Your password has been reset.');

        return redirect('/');
    }
}

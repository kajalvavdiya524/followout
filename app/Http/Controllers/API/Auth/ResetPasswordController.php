<?php

namespace App\Http\Controllers\API\Auth;

use Carbon;
use Str;
use Validator;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|exists:users,password_reset_token',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->input('email'))
                    ->where('password_reset_token', $request->input('token'))
                    ->where('password_reset_token_expires_at', '>=', now())
                    ->first();

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token has expired or does not exist',
            ], 422);
        }

        $user->password = bcrypt($request->input('password'));
        $user->remember_token = Str::random(60);
        $user->password_reset_token = null;
        $user->password_reset_token_expires_at = null;
        $user->save();

        return response()->json([ 'status' => 'OK' ]);
    }
}

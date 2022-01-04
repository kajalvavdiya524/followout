<?php

namespace App\Http\Controllers\API\Auth;

use Validator;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

       if ($validator->fails()) {
           return response()->json([
               'status' => 'error',
               'errors' => $validator->errors()
           ], 422);
       }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $user = User::where('email', $request->input('email'))->first();
        $user->sendPasswordResetEmail();

        return response()->json([ 'status' => 'OK' ]);
    }
}

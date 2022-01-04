<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\SupportRequest;
use Illuminate\Http\Request;
use Mail;
use Validator;

class HomeController extends Controller
{
    public function contactSupport(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        $rules = [
            'subject' => 'nullable|max:128',
            'message' => 'required|max:10000',
        ];

        if (!$authUser) {
            $rules['from_name'] = 'nullable|max:255';
            $rules['from_email'] = 'required|email|max:255';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $subject = $request->input('subject', null);
        $message = $request->input('message');

        $fromEmail = $request->input('from_email');
        $fromName = $request->input('from_name', null);

        Mail::send(new SupportRequest($message, $subject, $authUser, $fromEmail, $fromName));

        return response()->json(['status' => 'OK']);
    }
}

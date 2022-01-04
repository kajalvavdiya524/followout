<?php

namespace App\Http\Controllers;

use Mail;
use FollowoutHelper;
use App\User;
use App\Product;
use App\Followout;
use App\StaticContent;
use App\SalesRepresentative;
use App\Mail\SupportRequest;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function welcome()
    {
        // Same as in app/Exceptions/Handler.php
        $data['followouts'] = Followout::notGEOCoupon()->ongoingOrUpcoming()->orderBy('starts_at')->take(100)->get();
        $data['followouts'] = FollowoutHelper::filterFollowoutsForUser($data['followouts'], auth()->user());
        $data['geo_coupon_followouts'] = Followout::geoCoupon()->ongoingOrUpcoming()->orderBy('starts_at')->take(100)->get();
        $data['geo_coupon_followouts'] = FollowoutHelper::filterFollowoutsForUser($data['geo_coupon_followouts'], auth()->user());
        $data['followees'] = User::activated()->public()->followees()->orderBy('created_at', 'DESC')->take(25)->get();
        $data['followhosts'] = User::activated()->public()->followhosts()->subscribed()->orderBy('created_at', 'DESC')->take(25)->get();
        $data['landing_hero'] = StaticContent::where('name', 'landing_hero')->first();

        return view('welcome', compact('data'));
    }

    public function welcomeMobile()
    {
        return view('welcome-mobile');
    }

    public function about()
    {
        $content = StaticContent::where('name', 'about')->first();

        return view('about', compact('content'));
    }

    public function university()
    {
        $data['university'] = StaticContent::where('name', 'university')->first();

        return view('university', compact('data'));
    }

    public function pricing()
    {
        $data['landing_hero'] = StaticContent::where('name', 'landing_hero')->first();

        return view('pricing', compact('data'));
    }

    public function test()
    {
        return dd(
            'Request is using HTTPS: ' . request()->secure() ? 'Yes' : 'No',
        );
    }

    public function sendTestNotification()
    {
        $message = auth()->user()->notify(new \App\Notifications\TestNotification);

        session()->flash('toastr.success', 'Test notification has been sent.');

        return redirect('/');
    }

    public function contactSupport(Request $request)
    {
        $rules = [
            'subject' => 'nullable|max:128',
            'message' => 'required|max:10000',
        ];

        if (auth()->guest()) {
            $rules['from_name'] = 'nullable|max:255';
            $rules['from_email'] = 'required|email|max:255';
        }

        $request->validate($rules);

        $subject = $request->input('subject', null);
        $message = $request->input('message');

        $fromEmail = $request->input('from_email');
        $fromName = $request->input('from_name', null);

        Mail::send(new SupportRequest($message, $subject, auth()->user(), $fromEmail, $fromName));

        session()->flash('toastr.success', [
            'title' => 'Thank you for your message',
            'message' => 'Our support team will contact you shortly.',
        ]);

        return redirect()->back();
    }
}

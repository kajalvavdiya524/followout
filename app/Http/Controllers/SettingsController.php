<?php

namespace App\Http\Controllers;

use App\SalesRepresentative;
use App\SocialAccount;
use App\SubscriptionCode;
use FollowoutHelper;
use Illuminate\Http\Request;
use NotificationHelper;
use PaymentHelper;

class SettingsController extends Controller
{
    public function accountTab()
    {
        return view('settings.account');
    }

    public function securityTab()
    {
        return view('settings.security');
    }

    public function paymentsTab()
    {
        $payments = auth()->user()->payments()->orderBy('created_at', 'DESC')->get();

        return view('settings.payments', compact('payments'));
    }

    public function notificationsTab()
    {
        $notifications = NotificationHelper::listNotificationsForSettings();

        $platforms = collect([
            'db' => 'Website',
            'mail' => 'Email',
            // TODO: disabled until we use https://github.com/laravel-notification-channels/apn
            // 'mobile_push' => 'Push',
        ]);

        return view('settings.notifications', compact('notifications', 'platforms'));
    }

    public function updateNotificationSettings(Request $request)
    {
        $notifications = NotificationHelper::listNotificationsForSettings();

        $rules = NotificationHelper::makeValidationRules();

        $request->validate($rules);

        // Find and disable notifications for each platform
        foreach ($notifications as $notificationType => $notification) {
            foreach ($notification['platforms'] as $platform) {
                // TODO: disabled until we use https://github.com/laravel-notification-channels/apn
                if ($platform === 'mobile_push') continue;

                $enabled = $request->input('notifications_'.$platform.'.'.$notificationType, false);

                if (!$enabled) {
                    auth()->user()->disableNotification($notificationType, $platform);
                } else {
                    auth()->user()->enableNotification($notificationType, $platform);
                }
            }
        }

        session()->flash('toastr.success', 'Notification setting have been updated.');

        return redirect()->route('settings.notifications');
    }

    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|confirmed|min:8',
        ]);

        auth()->user()->password = bcrypt($request->input('password'));
        auth()->user()->save();

        session()->flash('toastr.success', 'Your password has been changed.');

        return redirect('/');
    }

    public function connectSocialAccount($provider)
    {
        $account = SocialAccount::where('provider', $provider)->where('user_id', auth()->user()->id)->first();

        if ($account) {
            session()->flash('toastr.error', 'Social network is already connected.');
            return redirect()->route('settings.account');
        }

        return redirect()->route('login.facebook');
    }

    public function disconnectSoicalAccount($provider)
    {
        $account = SocialAccount::where('provider', $provider)->where('user_id', auth()->user()->id)->first();

        if ($account) {
            $account->disconnect();
            session()->flash('toastr.success', 'Social network has been successfully disconnected.');
        } else {
            session()->flash('toastr.error', 'Social network is not connected.');
        }

        return redirect()->route('settings.account');
    }

    public function updateFollowoutSettings(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'flyer' => 'nullable|mimetypes:image/jpeg,image/png,image/gif,video/mp4,video/quicktime,video/x-m4v|max:100000',
            'removed_flyer' => 'nullable|array|max:1',
            'removed_flyer.*' => 'nullable|string|distinct',
            'show_default_followout' => 'nullable',
            'auto_show_default_followouts' => 'nullable',
            'autosubcribe_to_followhosts' => 'nullable',
            'available_for_promotion' => 'nullable',
        ]);

        if ($request->input('removed_flyer')) {
            $user->deleteDefaultFlyer();
        }

        if ($request->hasFile('flyer')) {
            $user->saveDefaultFlyer($request->file('flyer'));
        }

        if (($user->isFollowhost() && $user->subscribed()) || $user->isAdmin()) {
            if (!$user->hasDefaultFlyer()) {
                $user->saveDefaultFlyerFromUsername();
            }
        }

        if ($user->isFollowhost() && $user->subscribed() && $user->hasDefaultFollowout()) {
            if ($request->input('show_default_followout', null)) {
                FollowoutHelper::showDefaultFollowout($user->id);
            } else {
                FollowoutHelper::hideDefaultFollowout($user->id);
            }

            $user->auto_show_default_followouts = $request->input('auto_show_default_followouts', null) ? true : false;
        }

        if (!$user->isFollowhost()) {
            $user->autosubcribe_to_followhosts = $request->input('autosubcribe_to_followhosts', null) ? true : false;
        }

        $user->available_for_promotion = $request->input('available_for_promotion', null) ? true : false;
        $user->save();

        session()->flash('toastr.success', 'Your changes have been saved.');

        return redirect()->route('settings.account');
    }

    public function setSalesRepCode(Request $request)
    {
        if (auth()->user()->wasInvitedBySalesRep()) {
            return abort(403);
        }

        $request->validate([
            'code' => 'required|string|sales_rep_code_exists',
        ]);

        $salesRep = SalesRepresentative::where('code', $request->input('code'))->orWhere('promo_code', $request->input('code'))->first();

        $viaPromoCode = false;

        if ($request->input('code') === $salesRep->promo_code) {
            $viaPromoCode = true;
        }

        $salesRep->addReferredUser(auth()->user()->id, $viaPromoCode);

        session()->flash('toastr.success', 'Sales representative code has been set.');

        return redirect()->route('settings.payments');
    }

    public function useSubscriptionCode()
    {
        request()->validate([
            'subscription_code' => 'required|string|exists:subscription_codes,code'
        ]);

        $code = SubscriptionCode::where('code', request()->input('subscription_code'))->first();

        if ($code->activated_at) {
            session()->flash('toastr.error', 'Code has been redeemed already.');
            return redirect()->route('settings.payments');
        }

        $subscriptionCode = PaymentHelper::useChargebeeSubscriptionCode(request()->input('subscription_code'), auth()->user()->id);

        if ($subscriptionCode) {
            session()->flash('toastr.success', 'Subscription has activated successfully.');
        } else {
            session()->flash('toastr.error', 'Something went wrong.');
        }

        return redirect()->route('settings.payments');
    }
}

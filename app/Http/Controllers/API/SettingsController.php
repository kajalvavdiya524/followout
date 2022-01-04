<?php

namespace App\Http\Controllers\API;

use Validator;
use NotificationHelper;
use App\SocialAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function notifications()
    {
        $authUser = auth()->guard('api')->user();

        $notifications = NotificationHelper::listNotificationsForSettingsForUser($authUser);

        $platforms = collect([
            'db' => 'Website',
            'mail' => 'Email',
            // TODO: disabled until we use https://github.com/laravel-notification-channels/apn
            // 'mobile_push' => 'Push',
        ]);

        return response()->json([
            'status' => 'OK',
            'data' => [
                'platforms' => $platforms,
                'notifications' => $notifications,
            ]
        ]);
    }

    public function updateNotificationSettings(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        $notifications = NotificationHelper::listNotificationsForSettings();

        $rules = NotificationHelper::makeValidationRules();

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Find and disable notifications for each platform
        foreach ($notifications as $notificationType => $notification) {
            foreach ($notification['platforms'] as $platform) {
                // TODO: disabled until we use https://github.com/laravel-notification-channels/apn
                if ($platform === 'mobile_push') continue;

                $enabled = $request->input('notifications_'.$platform.'.'.$notificationType, false);

                if (!$enabled) {
                    $authUser->disableNotification($notificationType, $platform);
                } else {
                    $authUser->enableNotification($notificationType, $platform);
                }
            }
        }

        return response()->json([ 'status' => 'OK' ]);
    }

    public function changePassword(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $authUser->password = bcrypt($request->input('password'));
        $authUser->save();

        return response()->json([ 'status' => 'OK' ]);
    }

    public function disconnectSoicalAccount(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        $validator = Validator::make($request->all(), [
            'provider' => 'required|in:facebook',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $account = SocialAccount::where('provider', $request->input('provider'))->where('user_id', $authUser->id)->first();

        if (is_null($account)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Social account not found.',
            ], 404);
        }

        $account->disconnect();

        return response()->json([ 'status' => 'OK' ]);
    }
}

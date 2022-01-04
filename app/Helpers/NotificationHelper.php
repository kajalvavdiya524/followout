<?php

namespace App\Helpers;

use Arr;
use Carbon;
use App\User;
use App\Followout;
use App\Notification;
use App\Notifications\AttendeeInvitation;
use App\Notifications\FolloweeIntro;
use App\Notifications\FolloweeInvitation;
use App\Notifications\FollowoutExpiringSoon;
use App\Notifications\NewAutosubscribers;
use App\Notifications\NewCheckin;
use App\Notifications\NewMessage;
use App\Notifications\NewSubscriber;
use App\Notifications\PaymentCompleted;
use App\Notifications\PresentFollowoutRequest;
use App\Notifications\RewardNotReceived;
use App\Notifications\SubscriptionExpired;
use App\Notifications\SubscriptionExpiringSoon;
use App\Notifications\TestNotification;
use App\Notifications\VideoUploadFailed;
use App\Notifications\YouWereAutosubscribed;

/**
 * Supported notification platforms: db, mail, mobile_push
 */
class NotificationHelper
{
    /**
     * Notification types that have user avatar instead of icon.
     *
     * @var array
     */
    public const NOTIFICATIONS_WITH_AVATARS = [
        FolloweeIntro::class,
        NewMessage::class,
        NewSubscriber::class,
        PresentFollowoutRequest::class,
    ];

    public static function makeValidationRules()
    {
        return Arr::collapse([
            NotificationHelper::makeValidationRulesForPlatform('db'),
            NotificationHelper::makeValidationRulesForPlatform('mail'),
            NotificationHelper::makeValidationRulesForPlatform('mobile_push'),
        ]);
    }

    public static function makeValidationRulesForPlatform($platform)
    {
        $rules = [];

        $notifications = self::listNotificationsForSettings();

        if (app()->environment('production')) {
            $notifications->forget(TestNotification::class);
        }

        $notifications = $notifications->filter(function ($item, $key) use ($platform) {
            return in_array($platform, $item['platforms']);
        });

        $notificationTypes = $notifications->keys()->toArray();

        foreach ($notificationTypes as $type) {
            $rules['notifications_'.$platform.'.'.$type] = 'nullable|in:yes,on,1,true';
        }

        return $rules;
    }

    public static function listNotificationsForSettings()
    {
        $notifications = collect([
            FolloweeIntro::class => [
                'name' => 'Followee introduction',
                'description' => 'Receive a notification when user sends you an introduction.',
                'platforms' => ['db', 'mail', 'mobile_push'],
            ],
            PresentFollowoutRequest::class => [
                'name' => 'Present Followout request',
                'description' => 'Receive a notification when user requests to present your Followout.',
                'platforms' => ['db', 'mail', 'mobile_push'],
            ],
            AttendeeInvitation::class => [
                'name' => 'Invitation to attend Followout',
                'description' => 'Receive a notification when someone invites you to attend a Followout.',
                'platforms' => ['db', 'mail', 'mobile_push'],
            ],
            FolloweeInvitation::class => [
                'name' => 'Invitation to present Followout',
                'description' => 'Receive invites to present FollowOuts.',
                'platforms' => ['db', 'mail', 'mobile_push'],
            ],
            FollowoutExpiringSoon::class => [
                'name' => 'Followout expiring soon',
                'description' => 'Receive a notification when your GEO Coupon Followout is about to expire.',
                'platforms' => ['db', 'mail', 'mobile_push'],
            ],
            NewCheckin::class => [
                'name' => 'New checkin',
                'description' => 'Receive a notification when someone enters your followout.',
                'platforms' => ['db', 'mobile_push'],
            ],
            NewMessage::class => [
                'name' => 'New message',
                'description' => 'Receive a notification when someone sends you a message.',
                'platforms' => ['db', 'mobile_push'],
            ],
            NewSubscriber::class => [
                'name' => 'New subscriber',
                'description' => 'Receive a notification when someone subscribes to you.',
                'platforms' => ['db', 'mail', 'mobile_push'],
            ],
            NewAutosubscribers::class => [
                'name' => 'New autosubscribers',
                'description' => 'Receive a notification when users are automatically subscribed to you.',
                'platforms' => ['mail', 'mobile_push'],
            ],
            YouWereAutosubscribed::class => [
                'name' => 'You were autosubscribed',
                'description' => 'Receive a notification when you are automatically subscribed to a Followhost.',
                'platforms' => ['mail', 'mobile_push'],
            ],
            PaymentCompleted::class => [
                'name' => 'Payment completed',
                'description' => 'Receive a notification when your payment is completed successfully.',
                'platforms' => ['mail'],
            ],
            RewardNotReceived::class => [
                'name' => 'Reward program job reward not received',
                'description' => 'Receive a notification when followee did not receive the reward.',
                'platforms' => ['mail', 'mobile_push'],
            ],
            RewardProgramJobBecameRedeemable::class => [
                'name' => 'Reward program job completed',
                'platforms' => ['mail'],
                'description' => 'Receive a notification when you completed the reward program job or someone else completed your reward program job.',
            ],
            SubscriptionExpiringSoon::class => [
                'name' => 'Subscription expiring soon',
                'description' => 'Receive a notification when your subscription expires is about to expire.',
                'platforms' => ['mail'],
            ],
            SubscriptionExpired::class => [
                'name' => 'Subscription expired',
                'description' => 'Receive a notification when your subscription is expired.',
                'platforms' => ['mail'],
            ],
            VideoUploadFailed::class => [
                'name' => 'Video upload failed',
                'description' => 'Receive a notifications when your uploaded videos were\'t processed successfully.',
                'platforms' => ['mail'],
            ],
            TestNotification::class => [
                'name' => 'Test notification',
                'description' => 'Receive test notifications.',
                'platforms' => ['db', 'mail', 'mobile_push'],
            ],
        ]);

        if (app()->environment('production')) {
            $notifications->forget(TestNotification::class);
        }

        return $notifications;
    }

    public static function listNotificationsForSettingsForUser(User $user)
    {
        $notifications = self::listNotificationsForSettings();

        $notifications->transform(function ($notification, $notificationType) use ($user) {
            $notification['platforms'] = array_flip($notification['platforms']);

            foreach ($notification['platforms'] as $platform => $value) {
                $notification['platforms'][$platform] = $user->notificationEnabled($notificationType, $platform);
            }

            return $notification;
        });

        return $notifications;
    }

    public static function iconClass(Notification $notification)
    {
        switch ($notification->data['type']) {
            case TestNotification::class:
                $icon = 'fas fa-bug';
                break;
            case FolloweeIntro::class:
            case NewSubscriber::class:
                $icon = 'fas fa-user';
                break;
            case AttendeeInvitation::class:
            case FolloweeInvitation::class:
            case PresentFollowoutRequest::class:
                $icon = 'far fa-envelope';
                break;
            case NewMessage::class:
                $icon = 'fas fa-comments';
                break;
            default:
                $icon = 'fas fa-bell';
                break;
        }

        return $icon;
    }

    public static function avatarURL(Notification $notification)
    {
        if (!in_array($notification->data['type'], static::NOTIFICATIONS_WITH_AVATARS)) {
            return null;
        }

        $paramName = 'user';
        $modelClass = User::class;

        if ($notification->data['type'] === NewMessage::class) {
            $paramName = 'chat_id';
        }

        if (isset($notification->data['action_parameters'][$paramName])) {
            $model = $modelClass::find($notification->data['action_parameters'][$paramName]);

            if ($model) {
                return $model->avatarURL();
            }

            return (new $modelClass)->defaultAvatarURL();
        }

        return null;
    }

    public static function hasAvatar(Notification $notification)
    {
        return in_array($notification->data['type'], static::NOTIFICATIONS_WITH_AVATARS) && !is_null(static::avatarURL($notification));
    }

    public static function actionText(Notification $notification)
    {
        if (!$notification->hasAction()) {
            return null;
        }

        switch ($notification->data['type']) {
            case TestNotification::class:
                $text = 'Go to website';
                break;
            case FolloweeIntro::class:
            case NewSubscriber::class:
            case NewMessage::class:
                $text = 'View user';
                break;
            case NewCheckin::class:
            case AttendeeInvitation::class:
            case FolloweeInvitation::class:
            case PresentFollowoutRequest::class:
                $text = 'View Followout';
                break;
            default:
                $text = 'View';
                break;
        }

        return $text;
    }

    public static function actionUrl(Notification $notification)
    {
        if (!$notification->hasAction()) {
            return null;
        }

        $params = $notification->getActionParameters();

        try {
            switch ($notification->data['type']) {
                case TestNotification::class:
                    return url('/');
                case FolloweeIntro::class:
                case NewSubscriber::class:
                case NewMessage::class:
                    return route('users.show', $params);
                case FolloweeInvitation::class:
                    return route('followouts.invitation.manage', $params);
                case AttendeeInvitation::class:
                case FollowoutExpiringSoon::class:
                case NewCheckin::class:
                case PresentFollowoutRequest::class:
                    return route('followouts.show', $params);
            }
        } catch (\Exception $e) {
            return null;
        }

        return url('/');
    }
}

@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Notifications
                            </div>
                        </div>
                        <div class="Block__body Block__body--no-padding">
                            @if ($notifications->total())
                                <hr style="margin-top: 0 !important; border-top: none !important;">

                                <div class="form-group clearfix">
                                    <div class="col-xs-12 text-center">
                                        @if ($unreadCount)
                                            <a href="{{ route('notifications.read-all') }}" class="Button Button--sm Button--danger">
                                                Mark all as read
                                            </a>
                                        @endif
                                        <button type="submit" form="remove_all_notification_form" class="Button Button--sm Button--danger">
                                            Remove all
                                        </button>
                                        <form id="remove_all_notification_form" action="{{ route('notifications.destroy.all') }}" method="POST">
                                            @method('DELETE')
                                            @csrf
                                        </form>
                                    </div>
                                </div>

                                <hr style="margin-bottom: 0 !important;">
                            @endif
                            <div class="NotificationList">
                                @forelse ($notifications as $notification)
                                    <div class="NotificationListItem {{ $notification->isUnread() ? 'NotificationListItem--unread' : '' }}">
                                        @if (NotificationHelper::hasAvatar($notification))
                                            <div class="NotificationListItem__avatar-wrap">
                                                <img src="{{ NotificationHelper::avatarURL($notification) }}">
                                            </div>
                                        @else
                                            <div class="NotificationListItem__icon-wrap">
                                                <i class="{{ NotificationHelper::iconClass($notification) }} NotificationListItem__icon"></i>
                                            </div>
                                        @endif
                                        <div class="NotificationListItem__content">
                                            @isset ($notification->data['title'])
                                                <div class="NotificationListItem__heading">
                                                    {{ $notification->data['title'] }}
                                                </div>
                                            @endisset
                                            <div class="NotificationListItem__description">
                                                {{ $notification->data['message'] }}
                                            </div>
                                            <div class="NotificationListItem__date-wrap">
                                                <div class="NotificationListItem__date" data-toggle="tooltip" title="{{ $notification->created_at->tz(session_tz())->format(config('followouts.date_format_date_time_string')) }}">
                                                    {{ $notification->created_at->tz(session_tz())->diffForHumans() }}
                                                </div>
                                            </div>
                                            @isset ($notification->data['action_parameters'])
                                                @php
                                                    $actionUrl = NotificationHelper::actionUrl($notification);
                                                @endphp

                                                @if ($actionUrl)
                                                    <div class="NotificationListItem__action">
                                                        <a href="{{ $actionUrl }}" class="Button Button--xs Button--primary">
                                                            {{ NotificationHelper::actionText($notification) }}
                                                        </a>
                                                    </div>
                                                @endif
                                            @endisset
                                        </div>
                                        <div class="NotificationListItem__manage-buttons">
                                            @if ($notification->isUnread())
                                                <div data-href="{{ route('api.notifications.read', ['notification' => $notification->id]) }}" class="Button Button--sm Button--danger NotificationListItem__mark-read-button">
                                                    Mark as read
                                                </div>
                                            @endif
                                            <a href="{{ route('notifications.destroy', ['notification' => $notification->id]) }}" class="Button Button--sm Button--danger NotificationListItem__remove-button" data-method="delete" data-token="{{ csrf_token() }}">
                                                Remove
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <div class="form-group">
                                        <div class="col-xs-12">
                                            <div class="text-muted text-center" style="padding: 30px 0;">There are no notifications yet.</div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>

                            @unless ($notifications->currentPage() === 1 && ($notifications->currentPage() === $notifications->lastPage() || $notifications->lastPage() === 0))
                                <div class="Pagination Pagination--inside-block">
                                    {{ $notifications->links() }}
                                </div>
                            @endunless
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

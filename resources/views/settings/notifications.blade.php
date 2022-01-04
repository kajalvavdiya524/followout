@extends('layouts.app')

@section('content')
    <div class="Section">
        <div class="container">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    @include('settings.tabs')

                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Notification settings
                            </div>
                        </div>
                        <div class="Block__body">
                            <form class="Form NotificationSettingsForm" role="form" method="POST" action="{{ route('settings.notifications.update') }}">
                                {{ csrf_field() }}

                                @foreach ($notifications as $notificationType => $notification)
                                    <div class="form-group form-group--last">
                                        <div class="NotificationSettingsForm__notification">
                                            <div class="NotificationSettingsForm__notification-name">
                                                {{ $notification['name'] }}
                                            </div>
                                            <div class="NotificationSettingsForm__notification-description">
                                                {{ $notification['description'] }}
                                            </div>
                                        </div>

                                        <div class="row">
                                            @foreach ($platforms as $platform => $platformName)
                                                @php
                                                    $platformDisabled = !in_array($platform, $notification['platforms']);
                                                @endphp
                                                <div class="col-sm-4">
                                                    <div class="NotificationSettingsForm__platform">
                                                        {{ $platformName }}
                                                    </div>
                                                    <div class="form-switcher">
                                                        <input id="notifications_{{ $platform }}_{{ $notificationType }}" type="checkbox" name="notifications_{{ $platform }}[{{ $notificationType }}]" class="Checkbox__input" {{ auth()->user()->notificationEnabled($notificationType, $platform) ? 'checked' : '' }} {{ $platformDisabled ? 'disabled' : '' }}>
                                                        <label for="notifications_{{ $platform }}_{{ $notificationType }}" class="switcher" style="{{ $platformDisabled ? 'opacity: 0.25;' : '' }}"></label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="row">
                                        <hr>
                                    </div>
                                @endforeach

                                <div class="form-group form-group--last">
                                    <div class="col-xs-12 text-center">
                                        <button type="submit" class="Button Button--danger">
                                            Save changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

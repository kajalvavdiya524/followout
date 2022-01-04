@component('mail::message')
Congratulations! You've been invited to present a Followout!

@component('mail::panel')
Hello.

You have been invited to present my Followout. If interested, please click the link below for more details.

Or contact me at your earliest convenience: {{ $followout->author->email }}{{ $followout->author->phone_number ? ', '.$followout->author->phone_number : null }}

Thanks,<br>
{{ $followout->author->name }}
@endcomponent

@component('mail::button', ['url' => is_null($followee) ? $followout->url(true) : $followee->url(true)])
View Followout
@endcomponent

@component('mail::subcopy')
@if (is_null($followee))
You were invited by {{ $followout->author->name }} ({{ $followout->author->email }}).
@else
Click here to manage your notification settings: {{ route('settings.notifications') }}
@endif
@endcomponent
@endcomponent

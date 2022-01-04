@component('mail::message')
Congratulations! You've been invited to attend a Followout!

@component('mail::panel')
Hello.

You have been invited to attend {{ $followout->title }}. If interested, please click the link below for more details.

Thanks,<br>
{{ $followout->author->name }}
@endcomponent

@component('mail::button', ['url' => $followout->url(true)])
View Followout
@endcomponent

@component('mail::subcopy')
Click here to manage your notification settings: {{ route('settings.notifications') }}
@endcomponent
@endcomponent

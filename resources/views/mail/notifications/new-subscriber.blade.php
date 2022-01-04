@component('mail::message')
Congratulations! {{ $subscriber->name }} has become your subscriber!

@component('mail::button', ['url' => $subscriber->url()])
View user
@endcomponent

@component('mail::subcopy')
Click here to manage your notification settings: {{ route('settings.notifications') }}
@endcomponent
@endcomponent

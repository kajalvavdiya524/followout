@component('mail::message')
{{ $user->isFollowee() ? 'Followee' : 'User' }} requested to review {{ $user->isMale() ? 'his' : 'her' }} profile.

@component('mail::panel')
Hello.

I would like to introduce myself. If interested, please click link below to review my profile.

Or contact me at your earliest convenience: {{ $user->email }}{{ $user->phone_number ? ', '.$user->phone_number : null }}

Thanks,<br>
{{ $user->name }}
@endcomponent

@component('mail::button', ['url' => $user->url()])
View {{ $user->isFollowee() ? 'followee' : 'user' }}
@endcomponent

@component('mail::subcopy')
Click here to manage your notification settings: {{ route('settings.notifications') }}
@endcomponent
@endcomponent

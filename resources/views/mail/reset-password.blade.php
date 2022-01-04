@component('mail::message')
Hello.

We've received a request to reset your password.

Password reset verification code: <strong>{{ $token }}</strong>

Click this button or enter this verification code in the mobile app.

@component('mail::button', ['url' => route('password.reset', ['token' => $token])])
Reset password
@endcomponent

This verification code will be active for 15 minutes only.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

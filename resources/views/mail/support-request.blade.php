@component('mail::message')
# {{ $subject }}

@if ($fromName)
Submitted by <strong>{{ $fromName }}</strong> — {{ $fromEmail }}
@else
Submitted by {{ $fromEmail }}
@endif

@component('mail::panel')
@if ($messageSubject)
<strong>{{ $messageSubject }}</strong>
<br>
@endif
{{ $message }}
@endcomponent

@component('mail::button', ['url' => $url, 'color' => 'success'])
    Reply via email
@endcomponent

@if ($userUrl)
@component('mail::button', ['url' => $userUrl])
    View user
@endcomponent
@endif
@endcomponent

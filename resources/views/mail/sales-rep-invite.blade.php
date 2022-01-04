@component('mail::message')
# {{ $subject }}

@component('mail::panel')
{{ $message }}
@endcomponent

@component('mail::button', ['url' => $url, 'color' => 'error'])
    Learn more
@endcomponent
@endcomponent

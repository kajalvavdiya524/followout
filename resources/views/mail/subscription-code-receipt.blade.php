@component('mail::message')
Hello!

Thanks for purchasing the subscription code for {{ $subscriptionCode->plan_id === 'followouts-pro-yearly' ? 'Followouts Pro (1 year)' : 'Followouts Pro (1 month)' }}!

Your subscription code is:
@component('mail::panel')
{{ $subscriptionCode->code }}
@endcomponent

Next steps: click this link to use your subscription code and register as a business.
@component('mail::button', ['url' => route('register.business', ['subscription_code' => $subscriptionCode->code, 'aa_token' => $subscriptionCode->account_activation_token])])
Register as business
@endcomponent
@endcomponent

@extends('layouts.app')

@section('content')
    <div class="Section">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <h2 class="University__heading University__heading--text-left">
                        Thank you!
                    </h2>
                    <div class="University__item-description">
                        Subscription code has been purchased successfully!
                        <br>
                        Check your email for further instructions.
                    </div>
                    <br>
                    <div class="ButtonRow">
                        <a href="{{ route('register.business', ['subscription_code' => $subscriptionCode->code]) }}" class="Button Button--danger">Register and activate code</a>
                        <a href="{{ route('university') }}" class="Button Button--primary">Buy again</a>
                    </div>
                </div>
                <div class="col-xs-12 col-md-6">
                    <h2 class="University__heading University__heading--text-left">
                        Your subscription code
                    </h2>
                    <pre class="text-center text-bold text-primary">{{ $subscriptionCode->code }}</pre>
                </div>
            </div>
        </div>
    </div>
@endsection

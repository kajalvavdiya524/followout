@extends('layouts.app')

@section('content')
    <div class="Section">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">Activate your account</div>
                        </div>

                        <div class="Block__body">
                            Welcome to {{ config('app.name') }}!
                            <br>
                            <br>
                            Please activate your account via <strong>{{ auth()->user()->email }}</strong>.
                            <br>
                            <span class="text-muted">(Resend email or contact support if activation email not received.)</span>
                        </div>

                        <div class="Block__footer">
                            <a href="{{ action('UsersController@resendAccountActivationEmail') }}" class="Button Button--danger">Resend Email</a>
                            <div class="Button Button--default pull-right" data-toggle="modal" data-target="#contact-support-modal">Contact Support</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

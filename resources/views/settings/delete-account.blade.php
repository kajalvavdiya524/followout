@extends('layouts.app')

@section('content')
<div class="Section">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="Block">
                    <div class="Block__header">
                        <div class="Block__heading">Deactivate Your Account</div>
                    </div>

                    <div class="Block__body">
                        You are about to submit a request to deactivate your account.
                        <br>
                        <br>
                        Your account will be deactivated permanently.
                        <br>
                        <br>
                        Are you sure?
                    </div>

                    <div class="Block__footer">
                        <form action="{{ route('users.suicide.confirmed') }}" method="POST">
                            {{ csrf_field() }}
                            <button type="submit" class="Button Button--danger">Yes, deactivate my account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

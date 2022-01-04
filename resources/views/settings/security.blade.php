@extends('layouts.app')

@section('content')
    <div class="Section">
        <div class="container">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    @include('settings.tabs')

                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Change Password
                            </div>
                        </div>
                        <div class="Block__body">
                            <form class="Form form-horizontal" role="form" method="POST" action="{{ route('settings.password.change') }}">
                                {{ csrf_field() }}

                                <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                                    <label for="password" class="col-md-4 control-label">New Password</label>

                                    <div class="col-md-6">
                                        <input id="password" type="password" class="form-control" name="password" required>

                                        @if ($errors->has('password'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                                    <label for="password-confirm" class="col-md-4 control-label">Repeat Password</label>
                                    <div class="col-md-6">
                                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>

                                        @if ($errors->has('password_confirmation'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('password_confirmation') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group form-group--last">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button type="submit" class="Button Button--danger">
                                            Change Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

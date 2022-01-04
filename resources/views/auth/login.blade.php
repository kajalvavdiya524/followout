@extends('layouts.app')

@section('content')
<div class="Section Section--no-padding-mobile">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                <div class="Block">
                    <div class="Block__header">
                        <div class="Block__heading">
                            Login
                        </div>
                    </div>
                    <div class="Block__body">
                        <form class="Form Form--block-padding form-horizontal" role="form" method="POST" action="{{ route('login') }}">
                            {{ csrf_field() }}

                            <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label">E-Mail Address</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                                <label for="password" class="col-md-4 control-label">Password</label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control" name="password" required>

                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <div class="Checkbox">
                                        <input id="remember" type="checkbox" name="remember" class="Checkbox__input" {{ old('remember') ? 'checked' : '' }}>
                                        <label for="remember" class="Checkbox__label">Remember me</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-8 col-md-offset-4">
                                    <button type="submit" class="Button Button--danger">
                                        Login
                                    </button>
                                    {{--
                                    <a class="Button Button--facebook" href="{{ route('login.facebook') }}">
                                        <i class="fab fa-fw fa-facebook-f"></i>
                                        Login with Facebook
                                    </a>
                                    --}}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-8 col-md-offset-4">
                                    <a class="Button Button--link" href="{{ route('password.request') }}">
                                        Forgot Your Password?
                                    </a>
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

@push('scripts-footer')

@endpush

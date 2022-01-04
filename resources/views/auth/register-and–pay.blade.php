@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div style="margin-bottom: 15px;">
                        <img src="/img/price-intro.jpg" class="img-responsive" style="max-height: 110px;">
                    </div>
                </div>
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Register
                            </div>
                            @if ($data['plan'] === 'monthly')
                                <div class="Block__header-misc-info Block__header-misc-info--two-lines">
                                    <span class="text-bold">
                                        ${{ number_format($appData['monthlySubscription']->price, 2) }} / mo.
                                    </span>
                                    <br>
                                    <span class="text-muted">billed monthly, cancel anytime</span>
                                </div>
                            @elseif ($data['plan'] === 'annual')
                                <div class="Block__header-misc-info Block__header-misc-info--two-lines">
                                    <span class="text-bold">
                                        ${{ number_format($appData['yearlySubscription']->price / 12, 2) }} / mo.
                                    </span>
                                    <br>
                                    <span class="text-muted">billed annually, cancel anytime</span>
                                </div>
                            @elseif ($data['plan'] === 'basic')
                                <div class="Block__header-misc-info Block__header-misc-info--two-lines">
                                    <span class="text-bold">
                                        ${{ number_format($appData['basicSubscription']->price, 2) }}
                                    </span>
                                    <br>
                                    <span class="text-muted">billed only once</span>
                                </div>
                            @endif
                        </div>
                        <div class="Block__body">
                            <form class="Form Form--block-padding form-horizontal" method="POST" action="{{ route('register') }}" autocomplete="off">
                                {{ csrf_field() }}

                                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                    <label for="name" class="col-md-4 control-label is-required">Company Name</label>

                                    <div class="col-md-6">
                                        <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Example LLC" required>

                                        @if ($errors->has('name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('name') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                                    <label for="email" class="col-md-4 control-label is-required">E-Mail Address</label>

                                    <div class="col-md-6">
                                        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="johndoe@example.com" required>

                                        @if ($errors->has('email'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                                    <label for="password" class="col-md-4 control-label is-required">Password</label>

                                    <div class="col-md-6">
                                        <input id="password" type="password" class="form-control" name="password" required autocomplete="new-password">

                                        @if ($errors->has('password'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="password-confirm" class="col-md-4 control-label is-required">Confirm Password</label>

                                    <div class="col-md-6">
                                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('is_followhost') ? 'has-error' : '' }}">
                                    <label class="col-md-4 control-label">Become a Followhost</label>

                                    <div class="col-md-6">
                                        <div class="Checkbox Checkbox--disabled">
                                            <input id="is_followhost" type="checkbox" name="is_followhost" class="Checkbox__input" checked>
                                            <label class="Checkbox__label">Register as a Business</label>
                                        </div>
                                        <input type="hidden" name="plan" value="{{ $data['plan'] }}">
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('sales_rep') ? 'has-error' : '' }}" style="{{ (old('is_followhost')) ? null : 'display: none;' }}">
                                    <label for="sales_rep" class="col-md-4 control-label">Sales Representative Code</label>

                                    <div class="col-md-6">
                                        <input id="sales_rep" type="text" class="form-control" name="sales_rep" value="{{ old('sales_rep') }}" placeholder="Leave blank if you don't have the code">

                                        @if ($errors->has('sales_rep'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('sales_rep') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('terms') ? 'has-error' : '' }}">
                                    <div class="col-md-6 col-md-offset-4">
                                        <div class="Checkbox">
                                            <input id="terms" type="checkbox" name="terms" class="Checkbox__input" {{ old('terms') ? 'checked' : '' }}>
                                            <label for="terms" class="Checkbox__label">I understand and agree to the Followout LLC <a target="_blank" href="{{ route('about', ['#terms']) }}">Terms of Service</a> and <a target="_blank" href="{{ route('about', ['#privacy']) }}">Privacy Policy</a>.</label>

                                            @if ($errors->has('terms'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('terms') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button type="submit" class="Button Button--danger">
                                            Proceed to payment
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

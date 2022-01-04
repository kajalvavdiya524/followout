@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                New sales representative
                            </div>
                        </div>
                        <div class="Block__body">
                            <form class="Form form-horizontal" role="form" method="POST" action="{{ route('sales-reps.store') }}">
                                {{ csrf_field() }}

                                <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                                    <label for="email" class="col-md-4 control-label is-required">Email</label>

                                    <div class="col-md-8">
                                        <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>

                                        @if ($errors->has('email'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('first_name') ? 'has-error' : '' }}">
                                    <label for="first_name" class="col-md-4 control-label">First Name</label>

                                    <div class="col-md-8">
                                        <input id="first_name" name="first_name" class="form-control" value="{{ old('first_name') }}">

                                        @if ($errors->has('first_name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('first_name') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('last_name') ? 'has-error' : '' }}">
                                    <label for="last_name" class="col-md-4 control-label">Last Name</label>

                                    <div class="col-md-8">
                                        <input id="last_name" name="last_name" class="form-control" value="{{ old('last_name') }}">

                                        @if ($errors->has('last_name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('last_name') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
                                    <label for="phone" class="col-md-4 control-label">Phone</label>

                                    <div class="col-md-8">
                                        <input id="phone" type="text" class="form-control" name="phone_dummy" value="{{ old('phone') }}" placeholder="555 111 22 33">

                                        @if ($errors->has('phone'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('phone') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                    <label for="code" class="col-md-4 control-label is-required">Code</label>

                                    <div class="col-md-8">
                                        <input id="code" name="code" class="form-control" value="{{ old('code') }}" required>

                                        @if ($errors->has('code'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('code') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="promo_code" class="col-md-4 control-label">Code with Promo</label>

                                    <div class="col-md-8">
                                        <input id="promo_code" name="promo_code" class="form-control" placeholder="Will be set automatically" disabled>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button type="submit" class="Button Button--danger">
                                            Save
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

@push('scripts-footer')
    <script>
         $("#phone").intlTelInput({
             hiddenInput: 'phone',
         });
    </script>
@endpush

@extends('layouts.app')

@section('content')
    @if (is_null($content))
        <div id="agreement" class="Section Section--padding-md" style="margin-top: -15px;">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-md-10 col-md-offset-1">
                        <div class="Heading Heading--section Heading--blue">Sales Representative Agreement</div>
                        <p class="AboutSection__text">
                            Nothing here yet...
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div id="agreement" class="Section Section--padding-md" style="margin-top: -15px;">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-md-10 col-md-offset-1">
                        <div class="Heading Heading--section Heading--blue">Sales Representative Agreement</div>
                        <p class="AboutSection__text">
                            {!! nl2br(e($content->agreement)) !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Become Sales Representative
                            </div>
                        </div>
                        <div class="Block__body">
                            <form class="Form form-horizontal" role="form" method="POST" action="{{ route('accept-sales-rep-agreement', ['hash' => $rep->hash]) }}">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label for="email" class="col-md-4 control-label is-required">Email</label>

                                    <div class="col-md-8">
                                        <input id="email" type="email" name="email" class="form-control" value="{{ $rep->email }}" disabled>
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('first_name') ? 'has-error' : '' }}">
                                    <label for="first_name" class="col-md-4 control-label is-required">First Name</label>

                                    <div class="col-md-8">
                                        <input id="first_name" name="first_name" class="form-control" value="{{ old('first_name') ?: $rep->first_name }}" required>

                                        @if ($errors->has('first_name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('first_name') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('last_name') ? 'has-error' : '' }}">
                                    <label for="last_name" class="col-md-4 control-label is-required">Last Name</label>

                                    <div class="col-md-8">
                                        <input id="last_name" name="last_name" class="form-control" value="{{ old('last_name') ?: $rep->last_name }}" required>

                                        @if ($errors->has('last_name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('last_name') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
                                    <label for="phone" class="col-md-4 control-label is-required">Phone</label>

                                    <div class="col-md-8">
                                        <input id="phone" type="text" class="form-control" name="phone_dummy" value="{{ old('phone') ?: $rep->phone }}" placeholder="555 111 22 33">

                                        @if ($errors->has('phone'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('phone') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group  {{ $errors->has('terms') ? 'has-error' : '' }}">
                                    <div class="col-md-8 col-md-offset-4">
                                        <div class="Checkbox">
                                            <input id="terms" type="checkbox" name="terms" class="Checkbox__input" {{ old('terms') ? 'checked' : '' }}>
                                            <label for="terms" class="Checkbox__label">I understand and accept terms and conditions of the agreement</label>

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
                                            Accept and continue
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

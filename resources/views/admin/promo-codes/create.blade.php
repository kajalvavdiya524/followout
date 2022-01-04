@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                New promo code
                            </div>
                        </div>
                        <div class="Block__body">
                            <form class="Form form-horizontal" role="form" method="POST" action="{{ route('promo-codes.store') }}">
                                {{ csrf_field() }}

                                <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                    <label for="code" class="col-md-4 control-label is-required">Code</label>

                                    <div class="col-md-8">
                                        <input id="code" name="code" class="form-control" required value="{{ old('code') }}">

                                        @if ($errors->has('code'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('code') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('amount') ? 'has-error' : '' }}">
                                    <label for="amount" class="col-md-4 control-label is-required">Amount</label>

                                    <div class="col-md-8">
                                        <input id="amount" name="amount" class="form-control" required value="{{ old('amount') ?: '0.00' }}">

                                        @if ($errors->has('amount'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('amount') }}</strong>
                                            </span>
                                        @endif
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

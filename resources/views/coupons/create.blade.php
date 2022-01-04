@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                New GEO Coupon
                            </div>
                        </div>
                        <div class="Block__body">
                            <form class="Form form-horizontal" role="form" method="POST" action="{{ route('coupons.store') }}" enctype="multipart/form-data">
                                {{ csrf_field() }}

                                <div class="form-group {{ $errors->has('picture') ? 'has-error' : '' }}">
                                    <label class="col-md-4 control-label">GEO Coupon picture</label>

                                    <div class="col-md-6">
                                        <div class="ImageInputWithPreview">
                                            <div data-for="picture" class="ImageInputWithPreview__picture ImageInputWithPreview__picture--flyer ImageInputWithPreview__picture--geo–coupon"></div>
                                            <input id="picture" class="ImageInputWithPreview__input" type="file" name="picture" accept="image/x-png,image/jpeg">
                                        </div>
                                        <div class="ImageInputWithPreview__help-text">100x100px minimum</div>

                                        @if ($errors->has('picture'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('picture') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('qr_code') ? 'has-error' : '' }}">
                                    <label class="col-md-4 control-label">GEO Coupon QR code</label>

                                    <div class="col-md-6">
                                        <div class="ImageInputWithPreview">
                                            <div data-for="qr_code" class="ImageInputWithPreview__picture ImageInputWithPreview__picture--picture"></div>
                                            <input id="qr_code" class="ImageInputWithPreview__input" type="file" name="qr_code" accept="image/x-png,image/jpeg">
                                        </div>
                                        <div class="ImageInputWithPreview__help-text">100x100px minimum</div>

                                        @if ($errors->has('qr_code'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('qr_code') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                                    <label for="title" class="col-md-4 control-label is-required">GEO Coupon title</label>

                                    <div class="col-md-8">
                                        <input id="title" name="title" class="form-control" required value="{{ old('title') }}" placeholder="30% off drinks and food">

                                        @if ($errors->has('title'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('title') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                    <label for="description" class="col-md-4 control-label is-required">GEO Coupon details</label>

                                    <div class="col-md-8">
                                        <input id="description" name="description" class="form-control" required value="{{ old('description') }}" placeholder="Get 30% off for all drinks and food you'll find on this Followout!">

                                        @if ($errors->has('description'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('description') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('expires_at') || $errors->has('expires_at') ? 'has-error' : '' }}">
                                    <label class="col-md-4 control-label is-required">GEO Coupon expiration date</label>

                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <input id="expires_at" type="text" class="form-control datepicker" name="expires_at" value="{{ old('expires_at') ?: Carbon::now()->tz(session_tz())->format(config('followouts.date_format')) }}" placeholder="MM/DD/YYYY" required>
                                            </div>
                                        </div>

                                        @if ($errors->has('expires_at'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('expires_at') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="discount_type" class="col-md-4 control-label is-required">GEO Coupon discount type</label>

                                    <div class="col-md-8">
                                        <select id="discount_type" class="selectize" name="discount_type" required>
                                            <option value="2" {{ old('discount_type') == '2' ? 'selected' : null }}>Offer</option>
                                            <option value="0" {{ old('discount_type') == '0' ? 'selected' : null }}>%</option>
                                            <option value="1" {{ old('discount_type') == '1' ? 'selected' : null }}>$</option>
                                        </select>

                                        @if ($errors->has('discount_type'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('discount_type') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('discount') ? 'has-error' : '' }}">
                                    <label for="discount" class="col-md-4 control-label is-required">GEO Coupon discount value</label>

                                    <div class="col-md-8">
                                        <input id="discount" name="discount" class="form-control" value="{{ old('discount', '0.00') }}" placeholder="Enter a numeric value like 50 or 12.99" {{ old('discount_type', '2') === '2' ? 'readonly' : '' }} required>

                                        @if ($errors->has('discount'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('discount') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('promo_code') ? 'has-error' : '' }}">
                                    <label for="promo_code" class="col-md-4 control-label">GEO Coupon promo code</label>

                                    <div class="col-md-8">
                                        <input id="promo_code" name="promo_code" class="form-control" value="{{ old('promo_code') }}" placeholder="Your promo code for a coupon">

                                        @if ($errors->has('promo_code'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('promo_code') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                    <label for="code" class="col-md-4 control-label">GEO Coupon internal code</label>

                                    <div class="col-md-8">
                                        <input id="code" name="code" class="form-control" value="{{ old('code') }}" placeholder="Your internal code for a coupon">

                                        @if ($errors->has('code'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('code') }}</strong>
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

@push('scripts-footer')
    <script>
        $(function() {
            $('#discount_type').change(function() {
                if ($('#discount_type').val() === '2') {
                    $('#discount').val('0.00');
                    $('#discount').attr('readonly', true);
                } else {
                    $('#discount').attr('readonly', false);
                }
            });
        });
    </script>
@endpush

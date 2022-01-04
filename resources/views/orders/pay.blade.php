@extends('layouts.app')

@section('content')
<div class="Section Section--no-padding-mobile">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                <div class="Block">
                    <div class="Block__header">
                        <div class="Block__heading">
                            Checkout
                        </div>
                    </div>
                    <div class="Block__body">
                        <form id="pay-form" class="Form Form--block-padding form-horizontal" role="form" method="POST" action="{{ route('checkout.pay') }}">
                            {{ csrf_field() }}

                            @if (config('paypal.settings.mode', 'sandbox') === 'sandbox')
                                <div class="form-group" data-billing-group>
                                    <label class="col-md-4 control-label">Test card number</label>

                                    <div class="col-md-6">
                                        <input type="text" class="form-control" value="{{ '4669424246660779' }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group" data-billing-group>
                                    <label class="col-md-4 control-label">Test card type</label>

                                    <div class="col-md-6">
                                        <input type="text" class="form-control" value="{{ 'Visa' }}" readonly>
                                    </div>
                                </div>

                                <hr data-billing-group>
                            @endif

                            <div class="form-group {{ $errors->has('card_number') ? 'has-error' : '' }}" data-billing-group>
                                <label for="card_number" class="col-md-4 control-label is-required">Card Number</label>

                                <div class="col-md-6">
                                    <input id="card_number" type="text" class="form-control" name="card_number" value="{{ old('card_number') }}" placeholder="•••• •••• •••• ••••">

                                    @if ($errors->has('card_number'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('card_number') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('card_cvv') ? 'has-error' : '' }}" data-billing-group>
                                <label for="card_cvv" class="col-md-4 control-label is-required">Security code</label>

                                <div class="col-md-6">
                                    <input id="card_cvv" type="password" class="form-control" name="card_cvv" placeholder="•••" maxlength="4">

                                    @if ($errors->has('card_cvv'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('card_cvv') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('card_type') ? 'has-error' : '' }}" data-billing-group>
                                <label for="card_type" class="col-md-4 control-label is-required">Card type</label>

                                <div class="col-md-6">
                                    <select id="card_type" name="card_type" class="selectize">
                                        <option value="">Select card type...</option>
                                        @foreach ($cardTypes as $type => $name)
                                            <option value="{{ $type }}" {{ old('card_type') === $type ? 'selected' : null }}>{{ $name }}</option>
                                        @endforeach
                                    </select>

                                    @if ($errors->has('card_type'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('card_type') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('expires_on_month') || $errors->has('expires_on_year') ? 'has-error' : '' }}" data-billing-group>
                                <label for="expires_on_year" class="col-md-4 control-label is-required">Expires on</label>

                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-xs-6" style="padding-right: 7.5px;">
                                            <input id="expires_on_month" type="text" class="form-control" name="expires_on_month" value="{{ old('expires_on_month') }}" placeholder="MM">
                                        </div>
                                        <div class="col-xs-6" style="padding-left: 7.5px;">
                                            <input id="expires_on_year" type="text" class="form-control" name="expires_on_year" value="{{ old('expires_on_year') }}" placeholder="YYYY">
                                        </div>
                                    </div>

                                    @if ($errors->has('expires_on_month'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('expires_on_month') }}</strong>
                                        </span>
                                    @endif

                                    @if ($errors->has('expires_on_year'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('expires_on_year') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('first_name') || $errors->has('last_name') ? 'has-error' : '' }}" data-billing-group>
                                <label for="first_name" class="col-md-4 control-label is-required">Name on card</label>

                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-xs-6" style="padding-right: 7.5px;">
                                            <input id="first_name" type="text" class="form-control" name="first_name" value="{{ old('first_name') }}" placeholder="First name">
                                        </div>
                                        <div class="col-xs-6" style="padding-left: 7.5px;">
                                            <input id="last_name" type="text" class="form-control" name="last_name" value="{{ old('last_name') }}" placeholder="Last name">
                                        </div>
                                    </div>

                                    @if ($errors->has('first_name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('first_name') }}</strong>
                                        </span>
                                    @endif

                                    @if ($errors->has('last_name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('last_name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('country_code') ? 'has-error' : '' }}" data-billing-group>
                                <label for="country_code" class="col-md-4 control-label is-required">Billing Country</label>

                                <div class="col-md-6">
                                    <select id="country_code" class="selectize" name="country_code" data-live-search="true">
                                        <option value="">Select country...</option>
                                        @foreach ($data['countries'] as $country)
                                            <option value="{{ $country->code }}" {{ old('country_code') == $country->code ? 'selected' : null }}>{{ $country->name }}</option>
                                        @endforeach
                                    </select>

                                    @if ($errors->has('country_code'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('country_code') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('promo_code') ? 'has-error' : '' }}">
                                <label for="promo_code" class="col-md-4 control-label">Promo code</label>

                                <div class="col-md-6">
                                    <input id="promo_code" type="text" class="form-control" name="promo_code" value="{{ old('promo_code') }}" placeholder="Code">
                                    <div class="text-center text-muted" style="margin-top: 10px;">
                                        If you have a promo code you can enter it here.
                                    </div>

                                    @if ($errors->has('promo_code'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('promo_code') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button id="pay-button" type="submit" class="Button Button--danger pull-left">
                                        Pay ${{ number_format((float) $total, 2, '.', '') }}
                                    </button>
                                    <a href="{{ url('/') }}" class="Button Button--default pull-right">
                                        Cancel
                                    </a>
                                </div>
                            </div>

                            <hr>

                            <div class="text-center">
                                @foreach (auth()->user()->getCartProducts() as $product)
                                    <div class="form-group">
                                        <label class="col-xs-12 col-md-4 text-left">
                                            {{ $product->name }}
                                            <br>
                                            ${{ $product->price }}
                                        </label>

                                        <div class="col-xs-12 col-md-6 text-left">
                                            {{ $product->description }}
                                        </div>
                                    </div>
                                @endforeach
                                <div id="promo_code_container" class="form-group" style="display: none;">
                                    <label class="col-xs-12 col-md-4 text-left">
                                        Promo code
                                        <br>
                                        - $ <span id="promo_code_amount">0.00</span>
                                    </label>

                                    <div class="col-xs-12 col-md-6 text-left">
                                        Discount based on your promo code.
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label class="col-xs-12 col-md-4 text-left">
                                        $<span id="total_amount">{{ auth()->user()->getCartTotal() }}</span>
                                    </label>

                                    <div class="col-xs-12 col-md-6 text-left">
                                        Order total.
                                    </div>
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
        var orderTotal = "{{ auth()->user()->getCartTotal() }}";
        var orderTotalWithPromoCode = "{{ auth()->user()->getCartTotal() }}";

        $('#promo_code').change(function(){
            var promoCode = this.value;
            $.ajax({
                url: "{{ action('API\ValidationController@validatePromoCode') }}",
                type: 'POST',
                data: {
                    promo_code: promoCode,
                },
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('Authorization', "Bearer " + Laravel.api_token);
                },
                success: function(response) {
                    if (response.status == true) {
                        $.ajax({
                            url: "{{ action('API\SearchController@promoCode') }}",
                            type: 'POST',
                            data: {
                                promo_code: promoCode,
                            },
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader('Authorization', "Bearer " + Laravel.api_token);
                            },
                            success: function(response) {
                                orderTotalWithPromoCode = parseFloat(orderTotal) - parseFloat(response.data.promo_code.amount);
                                if (orderTotalWithPromoCode <= 0) {
                                    orderTotalWithPromoCode = '0.00';
                                    $('[data-billing-group]').hide();
                                } else {
                                    orderTotalWithPromoCode = orderTotalWithPromoCode.toFixed(2);
                                }

                                if (orderTotalWithPromoCode <= 0) {
                                    $('#pay-button').html('Get for free');
                                } else {
                                    $('#pay-button').html('Pay $'+orderTotalWithPromoCode);
                                }

                                $('#promo_code_amount').html(response.data.promo_code.amount);
                                $('#total_amount').html(orderTotalWithPromoCode);

                                $('#promo_code_container').show();

                                toastr.success('Promo code was successfully applied.');
                            }
                        });
                    } else {
                        if ($.trim($('#promo_code').val())) {
                            toastr.error('Promo code is incorrect.');
                        }

                        $('#promo_code').val('');
                        $('#promo_code_container').hide();
                        $('[data-billing-group]').show();
                        $('#total_amount').html(orderTotal);
                        $('#pay-button').html('Pay $'+orderTotal);
                    }
                }
            });
        });

        $(function(){
            var cardNumber = new Cleave('#card_number', {
                creditCard: true,
                onCreditCardTypeChanged: function (type) {
                    //
                }
            });

            var expiresOn = new Cleave('#expires_on_month', {
                date: true,
                datePattern: ['m']
            });

            var expiresOn = new Cleave('#expires_on_year', {
                date: true,
                datePattern: ['Y']
            });

            var cardCvv = new Cleave('#card_cvv', {
                numeral: true,
                numeralPositiveOnly: true,
                numericOnly: true,
                delimiter: '',
            });

            $('#promo_code').change();
        });
    </script>
@endpush

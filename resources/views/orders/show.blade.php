@extends('layouts.app')

@section('content')
<div class="Section">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="Block">
                    <div class="Block__header">
                        <div class="Block__heading">Payment successful</div>
                    </div>

                    <div class="Block__body">
                        Thanks for your order!
                        <br>
                        <br>
                        <div>
                            <strong>Payment ID:</strong> {{ $payment->payment_id }}
                        </div>
                        <div>
                            <strong>Date:</strong> {{ $payment->created_at->tz(session_tz())->format(config('followouts.date_format_date_time_string')) }}
                        </div>
                        <br>
                        @if ($payment->promo_code)
                            <div>
                                <strong>Total amount:</strong> ${{ number_format(collect($payment->products)->sum('price'), 2, '.', '') }}
                            </div>
                            <div>
                                <strong>Promo code amount:</strong> ${{ $payment->promo_code_amount }}
                            </div>
                        @endif
                        <div>
                            <strong>Paid total:</strong> ${{ $payment->amount }}
                        </div>
                        <hr>
                        @foreach ($payment->products as $product)
                            @php
                                $product = json_decode(json_encode($product));
                            @endphp
                            <div>
                                <strong>Product name:</strong> {{ $product->name }}
                            </div>
                            <div>
                                <strong>Product description:</strong> {{ $product->description }}
                            </div>
                            <div>
                                <strong>Product price:</strong> ${{ number_format($product->price, 2, '.', '') }}
                            </div>

                            @unless ($loop->last)
                                <hr>
                            @endunless
                        @endforeach
                    </div>

                    <div class="Block__footer">
                        <a href="{{ route('settings.payments') }}" class="Button Button--danger">Back to settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

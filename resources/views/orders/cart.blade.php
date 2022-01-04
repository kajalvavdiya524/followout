@extends('layouts.app')

@section('content')
<div class="Section">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="Heading Heading--blue Heading--section">Shopping Cart</div>
                @if ($cart->isEmpty())
                    <div class="text-muted">Your Shopping Cart is empty.</div>
                @else
                    <div class="table-responsive">
                        <table class="CartTable table table-bordered">
                            <tbody>
                                <tr class="CartTable__header">
                                    <th>
                                        <span>Name</span>
                                    </th>
                                    <th>
                                        <span>Description</span>
                                    </th>
                                    <th class="CartTable__col-price">
                                        <span>Price</span>
                                    </th>
                                    <th class="CartTable__col-remove">
                                        <span>Remove</span>
                                    </th>
                                </tr>
                                @foreach ($cart as $product)
                                    <tr>
                                        <td>
                                            <span>{{ $product->name }}</span>
                                        </td>
                                        <td>
                                            <span>{{ $product->description }}</span>
                                        </td>
                                        <td class="text-center text-semibold">
                                            <span>${{ number_format((float) $product->price, 2, '.', '') }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('cart.remove', ['id' => $product->id]) }}" class="Button Button--danger Button--xs">Delete</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="CartTotal">
                        Total: $<span class="CartTotal__sum">{{ number_format((float) $total, 2, '.', '') }}</span>
                    </div>

                    <div class="PayButton">
                        <a href="{{ route('checkout') }}" class="PayButton__button Button Button--danger">Pay Now</a>

                        <img src="{{ url('/img/accepted-cards.png') }}" class="PayButton__item PayButton__cards" alt="Accepted cards">
                        <div class="PayButton__item">
                            <script>SiteSeal("https://seal.networksolutions.com/images/netsolsiteseal.png", "NETSB", "none");</script>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts-header')
    <script src="https://seal.networksolutions.com/siteseal/javascript/siteseal.js"></script>
@endpush

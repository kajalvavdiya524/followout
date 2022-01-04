@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Products
                            </div>
                        </div>
                        <div class="Block__body">
                            @foreach ($products as $product)
                                <form class="Form form-horizontal" method="GET" action="{{ route('products.edit', ['product' => $product->id]) }}">
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Name</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $product->name }}" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Description</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $product->description }}" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Price</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ number_format($product->price, 2, '.', '') }}" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Product Type</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $product->type }}" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-6 col-md-offset-4">
                                            <a href="{{ route('products.edit', ['product' => $product->id]) }}" class="Button Button--danger">
                                                Edit product
                                            </a>
                                        </div>
                                    </div>
                                </form>

                                @unless ($loop->last)
                                    <hr>
                                    <br>
                                @endunless
                            @endforeach
                        </div>
                    </div>

                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Promo codes
                            </div>
                        </div>
                        <div class="Block__body">
                        @if ($promoCodes->count() > 0)
                        <div class="Form form-horizontal">
                        @endif
                            @forelse ($promoCodes as $promoCode)
                                <form class="Form form-horizontal" method="POST" action="{{ route('promo-codes.destroy', ['code' => $promoCode->id]) }}">
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}

                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Code</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $promoCode->code }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Amount</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $promoCode->amount }}" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-6 col-md-offset-4">
                                            <button type="submit" class="Button Button--danger">
                                                Delete code
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <hr>
                            @empty
                                <div class="form-group">
                                    <div class="col-md-8 col-md-offset-4">
                                        <div class="text-muted" style="padding: 30px 0;">Nothing here yet.</div>
                                    </div>
                                </div>
                            @endforelse
                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <a href="{{ route('promo-codes.create') }}" class="Button Button--primary">
                                        New promo code
                                    </a>
                                </div>
                            </div>
                            @if ($promoCodes->count() > 0)
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

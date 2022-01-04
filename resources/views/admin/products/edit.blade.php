@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Edit product
                            </div>
                        </div>
                        <div class="Block__body">
                            <form class="Form form-horizontal" role="form" method="POST" action="{{ route('products.update', ['product' => $product->id]) }}">
                                {{ method_field('PUT') }}
                                {{ csrf_field() }}

                                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                    <label for="name" class="col-md-4 control-label">Name</label>

                                    <div class="col-md-8">
                                        <input id="name" name="name" class="form-control" value="{{ old('name') ? old('name') : $product->name }}" required>

                                        @if ($errors->has('name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('name') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                    <label for="description" class="col-md-4 control-label">Description</label>

                                    <div class="col-md-8">
                                        <input id="description" name="description" type="text" class="form-control" value="{{ old('description') ? old('description') : $product->description }}" required>

                                        @if ($errors->has('description'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('description') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('price') ? 'has-error' : '' }}">
                                    <label for="price" class="col-md-4 control-label">Price</label>

                                    <div class="col-md-8">
                                        <input id="price" name="price" type="text" class="form-control" value="{{ old('price') ? old('price') : number_format($product->price, 2, '.', '') }}" required>

                                        @if ($errors->has('price'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('price') }}</strong>
                                            </span>
                                        @endif
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
                                        <button type="submit" class="Button Button--danger">
                                            Save changes
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

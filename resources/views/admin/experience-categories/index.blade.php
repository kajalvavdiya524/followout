@extends('layouts.app')

@section('content')
    {{-- Experience categories --}}
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Experience categories
                            </div>
                        </div>
                        <div class="Block__body">
                            @foreach ($experienceCategories as $category)
                                <form class="Form form-horizontal" method="GET" action="{{ route('app.experience-categories.edit', ['category' => $category->id]) }}">

                                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                        <label class="col-md-4 control-label">Name</label>

                                        <div class="col-md-8">
                                            <input class="form-control" value="{{ $category->name }}" disabled>

                                            @if ($errors->has('name'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('name') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-6 col-md-offset-4">
                                            <a href="{{ route('app.experience-categories.edit', ['category' => $category->id]) }}" class="Button Button--danger">
                                                Edit category
                                            </a>
                                        </div>
                                    </div>
                                </form>

                                @unless ($loop->last)
                                    <hr>
                                @endunless
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('page-title', 'Page not found | '.config('app.name'))

@section('content')
    <div class="Section Section--bg-gray" style="margin-top: -15px;">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <div class="alert alert-danger">
                        Oops, the page you are looking for doesn't exist.
                    </div>
                    @if (app()->environment('local'))
                        <div class="alert alert-danger">
                            {{ isset($data['exception']) && $data['exception']->getMessage() ? $data['exception']->getMessage() : 'Unknown error.' }}
                        </div>
                    @endif
                    <div class="alert alert-info">
                        You can browse followouts, followees and followhosts below.
                    </div>

                    <div class="text-center">
                        <a href="/" class="Button Button--danger">Return to home page</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('includes.welcome')
@endsection

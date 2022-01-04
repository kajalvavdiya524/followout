@extends('layouts.app')

@section('page-title', 'Oops, something went wrong | '.config('app.name'))

@section('content')
    <div class="Section" style="margin-top: -15px;">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <div class="alert alert-info">
                        Oops, something went wrong on our end.
                    </div>
                    <div class="alert alert-danger">
                        {{ isset($data['exception']) && $data['exception']->getMessage() ? $data['exception']->getMessage() : 'Unknown error.' }}
                    </div>

                    <div class="text-center">
                        <a href="/" class="Button Button--danger">Return to home page</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

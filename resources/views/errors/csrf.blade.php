@extends('layouts.app')

@section('content')
<div class="Section">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="Block">
                    <div class="Block__header">
                        <div class="Block__heading">Oops, an error has occured</div>
                    </div>

                    <div class="Block__body">
                        CSRF token is missing or invalid.
                        <br>
                        <br>
                        You've probaly been away for too long. Simply return to home page and you'll be good to go.
                    </div>

                    <div class="Block__footer">
                        <a href="/" class="Button Button--danger">Return to home page</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

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
                            Can't connect to database. Try refreshing the page.
                        </div>

                        <div class="Block__footer">
                            <a href="javascript:history.go(0);" class="Button Button--danger">Refresh page</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

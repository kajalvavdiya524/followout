@extends('layouts.app')

@section('content')
    <div class="Section">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Deployment in progress
                            </div>
                        </div>
                        <div class="Block__body text-center">
                            <p>
                                Deployment is in progress. Please go back in about 10-15 seconds.
                            </p>
                            <div>
                                <a href="{{ route('app.deploy') }}" class="Button Button--danger">
                                    Go back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

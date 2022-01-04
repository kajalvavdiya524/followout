@extends('layouts.app')

@section('content')
    <div class="Section">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">Redirecting...</div>
                        </div>

                        <div class="Block__body">
                            <div class="text-center">
                                <br>
                                <i class="fas fa-fw fa-2x fa-spinner fa-pulse"></i>
                                <br>
                                <br>
                                You'll be redirected to our payment provider in <span id="timer">10</span> seconds.
                                <br>
                                <br>
                                Your subscription will start immediately after the payment is complete.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts-footer')
    <script>
        var timerInterval;

        function startTimer(duration, display) {
            var timer = duration;

            timerInterval = setInterval(function () {
                seconds = parseInt(timer, 10);

                display.text(seconds);

                if (--timer < 0) {
                    clearInterval(timerInterval);

                    var url = "{{ $route }}";

                    // Redirect to Chargebee
                    window.location.replace(url);
                }
            }, 1000);
        }

        $(function() {
            startTimer(10, $('#timer'));
        });
    </script>
@endpush

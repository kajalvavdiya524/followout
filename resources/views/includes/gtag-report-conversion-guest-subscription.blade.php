@if (app()->environment('production'))
    @push('scripts-footer')
        <script>
            function gtag_report_conversion_and_redirect(url) {
                var callback = function() {
                    if (typeof(url) != 'undefined') {
                        window.location = url;
                    }
                };

                if (adblock) {
                    callback();
                } else {
                    gtag('event', 'conversion', {
                        'send_to': 'AW-816849754/tTTgCMn1on4Q2sbAhQM',
                        'event_callback': callback
                    });
                }

                return false;
            }

            function gtag_report_conversion() {
                    gtag('event', 'conversion', {
                        'send_to': 'AW-816849754/tTTgCMn1on4Q2sbAhQM',
                    });
                }

                return false;
            }
        </script>
    @endpush
@endif

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('includes.meta-tags')
    @include('includes.favicon')
    @include('includes.meta-properties')

    {{-- Styles --}}
    @include('includes.fonts.font-awesome')
    @include('includes.fonts.ubuntu')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/12.0.1/css/intlTelInput.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pickadate.js/3.6.3/compressed/themes/default.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pickadate.js/3.6.3/compressed/themes/default.date.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pickadate.js/3.6.3/compressed/themes/default.time.css">
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">

    {{-- Scripts --}}
    <script src="{{ mix('js/ads.js') }}"></script>

    <script>
        var adblock = true;

        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
            'api_token' => optional(auth()->user())->api_token
        ]) !!};
    </script>

    @include('includes.google-analytics')
    @include('includes.google-adwords')

    @if (app()->environment('production'))
        <script type='text/javascript' src='//platform-api.sharethis.com/js/sharethis.js#property=5a157fdb9168480012f73fb6&product=inline-share-buttons' async='async'></script>
    @else
        <script type="text/javascript" src="//platform-api.sharethis.com/js/sharethis.js#property=5a15cbaa3865ce001113be31&product=inline-share-buttons"></script>
    @endif

    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_API_KEY') }}&amp;libraries=places"></script>

    @stack('scripts-header')
</head>
<body>
    <div id="app">
        @include('includes.header')

        @yield('content')

        @include('includes.footer')
    </div>

    {{-- Scripts --}}
    <script src="{{ mix('js/app.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/12.0.1/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/12.0.1/js/utils.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pickadate.js/3.6.3/compressed/picker.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pickadate.js/3.6.3/compressed/picker.date.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pickadate.js/3.6.3/compressed/picker.time.js"></script>

    @stack('scripts-footer')

    @include('includes.toastr-notifications')

    @stack('modals')

    @auth
        @include('includes.modals.subscription-required')
        @include('includes.modals.subscription-upgrade-required')
    @endauth

    @unless (app()->environment('production'))
        <script>
            console.log(moment().format('LLLL') + ' - your time');
            console.log(moment().utc().format('LLLL') + ' - UTC time');
            console.log(jstz.determine().name() + ' - your timezone');
            console.log('{{ session_tz() }} - server timezone');
        </script>
    @endunless
</body>
</html>

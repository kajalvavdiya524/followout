@if (app()->environment('production'))
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-816849754"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'AW-816849754');
    </script>
@endif

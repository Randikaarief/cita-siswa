<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#036ffa">
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" type="image/png" href="{{ asset('images/favicon-64.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
@if (config('site.ga4_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('site.ga4_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', @json(config('site.ga4_id')));
    </script>
@endif

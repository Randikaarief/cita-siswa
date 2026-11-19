<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.head')
    <title>@yield('title') | {{ config('site.name') }}</title>
</head>
<body class="min-h-screen">
<main class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center gap-6 px-4 py-10">
    <div class="space-y-3 text-center">
        <img src="{{ asset('images/logo.png') }}" alt="{{ config('site.name') }}" width="360" height="296" class="mx-auto h-28 w-auto">
        <p class="text-sm font-bold text-muted">{{ config('site.tagline') }}</p>
        <p class="text-sm text-muted">
            Temukan info beasiswa dan lomba, kenalan dengan kakak alumni, dan siapkan CV serta esaimu.
            Pendaftaran beasiswa tetap lewat situs resmi penyelenggara.
        </p>
    </div>

    <div class="card card-pad space-y-5">
        @yield('content')
    </div>

    <p class="text-center text-xs text-muted">{{ config('site.disclaimer') }}</p>
</main>
</body>
</html>

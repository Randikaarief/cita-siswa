<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.head')
    <title>@yield('title', 'Beranda') | {{ config('site.name') }}</title>
</head>
<body class="min-h-screen flex flex-col">
@php
    $me = auth()->user();
    $links = [
        ['/', 'Beranda', request()->is('/')],
        ['/find-scholarship', 'Cari Beasiswa', request()->is('find-scholarship*')],
        ['/lomba', 'Lomba', request()->is('lomba*')],
        ['/alumni', 'Alumni', request()->is('alumni*')],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-line bg-white/90 backdrop-blur">
    <div class="page flex items-center justify-between gap-4 py-2.5">
        <a href="/" class="flex items-center gap-2.5 shrink-0" aria-label="{{ config('site.name') }}">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('site.name') }}" width="360" height="296" class="h-11 w-auto">
            <span class="hidden lg:block text-xs font-bold leading-tight text-muted">{{ config('site.tagline') }}</span>
        </a>

        <nav class="hidden md:flex items-center gap-1" aria-label="Menu utama">
            @foreach ($links as [$href, $label, $active])
                <a href="{{ $href }}" class="nav-link {{ $active ? 'nav-link-active' : '' }}" @if($active) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <details data-dropdown class="relative">
                <summary class="flex cursor-pointer items-center gap-2 rounded-full p-1 pr-3 hover:bg-brand-50" aria-label="Menu akun">
                    @if ($me->photo_path)
                        <img src="{{ $me->avatarUrl() }}" alt="" class="size-9 rounded-full object-cover">
                    @else
                        <span class="avatar">{{ \App\Support\Palette::initial($me->name) }}</span>
                    @endif
                    <span class="hidden sm:block max-w-28 truncate text-sm font-bold">{{ $me->name }}</span>
                    @if (($unreadAlerts ?? 0) > 0)
                        <span class="badge badge-yellow">{{ $unreadAlerts }}</span>
                    @endif
                </summary>
                <div class="card absolute right-0 mt-2 w-60 p-2 shadow-lg">
                    <div class="md:hidden border-b border-line pb-2 mb-2 space-y-0.5">
                        @foreach ($links as [$href, $label, $active])
                            <a href="{{ $href }}" class="nav-link block {{ $active ? 'nav-link-active' : '' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                    <a href="/dashboard" class="nav-link block">Dashboard</a>
                    <a href="/riwayat" class="nav-link block">Riwayat</a>
                    <a href="/alert" class="nav-link flex items-center justify-between">Notifikasi
                        @if (($unreadAlerts ?? 0) > 0)<span class="badge badge-yellow">{{ $unreadAlerts }}</span>@endif
                    </a>
                    <a href="/settings/personal" class="nav-link block">Pengaturan</a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-line pt-1">
                        @csrf
                        <button type="submit" class="nav-link block w-full text-left cursor-pointer">Keluar</button>
                    </form>
                </div>
            </details>
        </div>
    </div>
</header>

<main class="flex-1">
    <div class="page py-6 sm:py-8 space-y-6">
        @include('partials.flash')
        @include('partials.verify-banner')
        @yield('content')
    </div>
</main>

@include('layouts.footer')
</body>
</html>

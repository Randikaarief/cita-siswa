<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.head')
    <title>@yield('title', 'Panel') | {{ config('site.name') }}</title>
</head>
<body class="min-h-screen flex flex-col">
@php
    $me = auth()->user();
    // [alamat, label, pola nama route untuk menandai menu aktif]
    $nav = $me->isAdmin()
        ? [
            ['/admin', 'Dashboard', 'admin.dashboard'],
            ['/admin/funnel', 'Corong', 'admin.funnel*'],
            ['/admin/scholarships', 'Beasiswa', 'admin.scholarships.*'],
            ['/admin/competitions', 'Lomba', 'admin.competitions.*'],
            ['/admin/mentors', 'Mentor', 'admin.mentors.*'],
            ['/admin/testimonials', 'Testimoni', 'admin.testimonials*'],
            ['/admin/students', 'Siswa', 'admin.students*'],
            ['/admin/announcements', 'Pengumuman', 'admin.announcements*'],
            ['/admin/samples', 'Data contoh', 'admin.samples*'],
        ]
        : [
            ['/mentor', 'Dashboard', 'mentor.dashboard'],
            ['/mentor/requests', 'Pengajuan', 'mentor.requests*'],
            ['/mentor/profile', 'Profil', 'mentor.profile*'],
            ['/mentor/testimonials', 'Testimoni', 'mentor.testimonials*'],
        ];
@endphp
<header class="sticky top-0 z-40 border-b border-line bg-white/90 backdrop-blur">
    <div class="page">
        <div class="flex items-center justify-between gap-4 py-2.5">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('site.name') }}" width="360" height="296" class="h-10 w-auto">
                <span class="badge {{ $me->isAdmin() ? 'badge-yellow' : 'badge-blue' }}">{{ $me->isAdmin() ? 'Admin' : 'Mentor' }}</span>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <span class="hidden sm:inline font-bold">{{ $me->name }}</span>
                <a href="{{ route('panel.password') }}" class="link">Ganti password</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-line btn-sm">Keluar</button>
                </form>
            </div>
        </div>
        <nav class="flex gap-1 overflow-x-auto pb-2" aria-label="Menu panel">
            @foreach ($nav as [$href, $label, $pattern])
                <a href="{{ $href }}" class="nav-link whitespace-nowrap {{ request()->routeIs($pattern) ? 'nav-link-active' : '' }}">{{ $label }}</a>
            @endforeach
        </nav>
    </div>
</header>

<main class="flex-1">
    <div class="page py-6 sm:py-8 space-y-6">
        @include('partials.flash')
        @yield('content')
    </div>
</main>
</body>
</html>

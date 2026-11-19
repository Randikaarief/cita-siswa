@extends('layouts.app')
@section('title', 'Beranda')

@section('content')
{{-- ===== Hero ===== --}}
<section class="relative overflow-hidden rounded-[2rem] bg-white border border-line">
    <div class="absolute -right-16 -top-16 size-64 rounded-full bg-sun-400/30" aria-hidden="true"></div>
    <div class="absolute -left-10 -bottom-20 size-56 rounded-full bg-brand-100" aria-hidden="true"></div>
    <div class="relative grid items-center gap-8 p-6 sm:p-10 md:grid-cols-5">
        <div class="md:col-span-3 space-y-5">
            <p class="eyebrow">{{ config('site.tagline') }}</p>
            <h1 class="h1">Kuliah itu bisa, kok. Yuk, cari tahu jalannya.</h1>
            <p class="text-base text-muted sm:text-lg">
                Banyak teman kita tidak lanjut kuliah bukan karena tidak mampu, tapi karena belum tahu info beasiswa
                dan bingung harus bertanya ke siapa. Di sini kami temani, dari cari info sampai berkasmu siap.
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="/find-scholarship" class="btn btn-primary">Cari Beasiswa</a>
                <a href="/lomba" class="btn btn-soft">Lihat Lomba</a>
            </div>
            <p class="text-xs text-muted">Kami bukan penyelenggara beasiswa. Daftar tetap lewat situs resmi penyelenggara.</p>
        </div>
        <div class="md:col-span-2 flex justify-center">
            <img src="{{ asset('images/icon-192.png') }}" alt="" width="192" height="192" class="size-40 sm:size-52 drop-shadow-lg">
        </div>
    </div>
</section>

{{-- ===== Kenapa ===== --}}
<section class="grid gap-4 md:grid-cols-3">
    <div class="card card-pad space-y-2">
        <span class="badge badge-blue">1</span>
        <h2 class="h3">Info yang dirangkum</h2>
        <p class="text-sm text-muted">Beasiswa dan lomba dikumpulkan dalam satu tempat, lengkap dengan link ke situs resminya.</p>
    </div>
    <div class="card card-pad space-y-2">
        <span class="badge badge-yellow">2</span>
        <h2 class="h3">Kenalan dengan alumni</h2>
        <p class="text-sm text-muted">Lihat cerita kakak-kakak yang pernah ada di posisimu. Mereka mulai dari nol, sama seperti kamu.</p>
    </div>
    <div class="card card-pad space-y-2">
        <span class="badge badge-blue">3</span>
        <h2 class="h3">Siapkan CV dan esai</h2>
        <p class="text-sm text-muted">Simpan draf berkasmu, lalu minta masukan dari mentor yang bersedia. Mentor membantu secara sukarela.</p>
    </div>
</section>

{{-- ===== Cara kerja ===== --}}
<section class="space-y-4">
    <div>
        <p class="eyebrow">Cara kerjanya</p>
        <h2 class="h2">Empat langkah santai</h2>
    </div>
    <ol class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Cari info', 'Lihat beasiswa dan lomba yang cocok untukmu.'],
            ['Kenalan', 'Baca profil alumni dan cerita mereka.'],
            ['Siapkan berkas', 'Unggah draf CV dan esai, minta masukan mentor.'],
            ['Daftar di situs resmi', 'Tombol Apply mengantarmu ke situs penyelenggara.'],
        ] as $i => [$title, $text])
            <li class="card card-pad flex gap-3">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-sun-400 font-extrabold">{{ $i + 1 }}</span>
                <div>
                    <p class="font-extrabold">{{ $title }}</p>
                    <p class="text-sm text-muted">{{ $text }}</p>
                </div>
            </li>
        @endforeach
    </ol>
</section>

{{-- ===== Beasiswa ===== --}}
@if ($scholarships->isNotEmpty())
<section class="space-y-4">
    <div class="flex items-end justify-between gap-3">
        <div>
            <p class="eyebrow">Beasiswa</p>
            <h2 class="h2">Mulai dari sini</h2>
        </div>
        <a href="/find-scholarship" class="link text-sm">Lihat semua</a>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($scholarships as $s)
            @include('partials.scholarship-card', ['s' => $s])
        @endforeach
    </div>
    <p class="text-xs text-muted">Jadwal dan syarat bisa berubah. Selalu cek di situs resmi penyelenggara.</p>
</section>
@endif

{{-- ===== Lomba ===== --}}
@if ($competitions->isNotEmpty())
<section class="space-y-4">
    <div class="flex items-end justify-between gap-3">
        <div>
            <p class="eyebrow">Lomba</p>
            <h2 class="h2">Perkuat portofoliomu</h2>
        </div>
        <a href="/lomba" class="link text-sm">Lihat semua</a>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($competitions as $c)
            @include('partials.competition-card', ['c' => $c])
        @endforeach
    </div>
</section>
@endif

{{-- ===== Alumni ===== --}}
@if ($mentors->isNotEmpty())
<section class="space-y-4">
    <div class="flex items-end justify-between gap-3">
        <div>
            <p class="eyebrow">Alumni</p>
            <h2 class="h2">Kenalan dengan kakak alumni</h2>
        </div>
        <a href="/alumni" class="link text-sm">Lihat semua</a>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($mentors as $m)
            @include('partials.mentor-card', ['m' => $m])
        @endforeach
    </div>
</section>
@endif

{{-- ===== Ajakan ===== --}}
<section class="card card-pad flex flex-col items-start gap-4 !bg-brand-500 !border-brand-500 text-white sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="h2">Mulai dari satu langkah kecil</h2>
        <p class="text-brand-100">Buka satu beasiswa, baca syaratnya, dan lihat apakah cocok untukmu.</p>
    </div>
    <a href="/find-scholarship" class="btn btn-accent shrink-0">Cari Beasiswa</a>
</section>
@endsection

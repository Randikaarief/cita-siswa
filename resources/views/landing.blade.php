<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.head')
    <title>{{ config('site.name') }} | {{ config('site.tagline') }}</title>
    <meta name="description" content="Info beasiswa dan lomba, kenalan dengan kakak alumni, dan siapkan CV serta esaimu. {{ config('site.tagline') }}.">
</head>
<body class="min-h-screen">
<main class="mx-auto flex min-h-screen w-full max-w-3xl flex-col justify-center gap-6 px-4 py-10">

    {{-- ===== Sambutan ===== --}}
    <section class="relative overflow-hidden rounded-[2rem] border border-line bg-white">
        <div class="absolute -right-16 -top-16 size-56 rounded-full bg-sun-400/30" aria-hidden="true"></div>
        <div class="absolute -left-10 -bottom-20 size-52 rounded-full bg-brand-100" aria-hidden="true"></div>
        <div class="relative space-y-5 p-6 text-center sm:p-10">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('site.name') }}" width="360" height="296" class="mx-auto h-32 w-auto sm:h-40">
            <p class="eyebrow">{{ config('site.tagline') }}</p>
            <h1 class="h1">Kuliah itu bisa, kok. Yuk, cari tahu jalannya.</h1>
            <p class="mx-auto max-w-xl text-base text-muted sm:text-lg">
                Banyak teman kita tidak lanjut kuliah bukan karena tidak mampu, tapi karena belum tahu info beasiswa
                dan bingung harus bertanya ke siapa. Masuk dulu, ya. Kami temani dari cari info sampai berkasmu siap.
            </p>
            <div class="flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('login') }}" class="btn btn-primary">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-accent">Daftar, yuk</a>
            </div>
            <p class="text-xs text-muted">Belum punya akun? Daftar saja, cuma butuh nama, email, dan password.</p>
        </div>
    </section>

    {{-- ===== Yang kamu dapat ===== --}}
    <section class="grid gap-4 sm:grid-cols-3">
        <div class="card card-pad space-y-2">
            <span class="badge badge-blue">1</span>
            <h2 class="h3">Info yang dirangkum</h2>
            <p class="text-sm text-muted">Beasiswa dan lomba dikumpulkan di satu tempat, lengkap dengan link ke situs resminya.</p>
        </div>
        <div class="card card-pad space-y-2">
            <span class="badge badge-yellow">2</span>
            <h2 class="h3">Kenalan dengan alumni</h2>
            <p class="text-sm text-muted">Lihat cerita kakak-kakak yang pernah ada di posisimu.</p>
        </div>
        <div class="card card-pad space-y-2">
            <span class="badge badge-blue">3</span>
            <h2 class="h3">Siapkan CV dan esai</h2>
            <p class="text-sm text-muted">Simpan drafmu, lalu minta masukan dari mentor yang bersedia. Mentor membantu secara sukarela.</p>
        </div>
    </section>

    <p class="text-center text-xs text-muted">{{ config('site.disclaimer') }}</p>
</main>
</body>
</html>

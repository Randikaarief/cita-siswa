@extends('layouts.app')
@section('title', $a['nama'])

@section('content')
<a href="/alumni" class="link text-sm">&larr; Kembali ke daftar</a>

<section class="card card-pad flex flex-col gap-5 sm:flex-row sm:items-center">
    <span class="avatar !size-20 !text-3xl">{{ \App\Support\Palette::initial($a['nama']) }}</span>
    <div class="space-y-2">
        <h1 class="h2">{{ $a['nama'] }}</h1>
        @if ($a['tipe'])<p class="text-muted">{{ $a['tipe'] }}</p>@endif
        <div class="flex flex-wrap gap-1.5">
            @if ($a['kategori'])<span class="badge badge-blue">{{ $a['kategori'] }}</span>@endif
            @if ($a['lokasi'])<span class="badge badge-gray">{{ $a['lokasi'] }}</span>@endif
            @if ($a['is_willing_to_help'])<span class="badge badge-green">Bersedia memberi masukan</span>@endif
        </div>
    </div>
</section>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        @if ($a['beasiswa'])
            <section class="card card-pad space-y-1">
                <h2 class="h3">Beasiswa yang diterima</h2>
                <p class="text-muted">{{ $a['beasiswa'] }}</p>
            </section>
        @endif
        @if ($a['description'])
            <section class="card card-pad space-y-2">
                <h2 class="h3">Tentang</h2>
                <p class="text-muted whitespace-pre-line">{{ $a['description'] }}</p>
            </section>
        @endif
        @if ($a['benefits'])
            <section class="card card-pad space-y-2">
                <h2 class="h3">Yang bisa dibantu</h2>
                <p class="text-muted whitespace-pre-line">{{ $a['benefits'] }}</p>
            </section>
        @endif
        @if ($a['youtube'])
            <section class="card card-pad space-y-3">
                <h2 class="h3">Cerita {{ $a['nama'] }}</h2>
                <div class="aspect-video overflow-hidden rounded-xl bg-slate-100">
                    <iframe class="size-full" loading="lazy" src="https://www.youtube-nocookie.com/embed/{{ $a['youtube'] }}" title="Video {{ $a['nama'] }}" allowfullscreen></iframe>
                </div>
            </section>
        @endif
        @foreach ($testimonials as $t)
            <section class="card card-pad space-y-3">
                @if ($t->quote)<p class="text-muted whitespace-pre-line">&ldquo;{{ $t->quote }}&rdquo;</p>@endif
                @if ($t->youtube)
                    <div class="aspect-video overflow-hidden rounded-xl bg-slate-100">
                        <iframe class="size-full" loading="lazy" src="https://www.youtube-nocookie.com/embed/{{ $t->youtube }}" title="Testimoni {{ $a['nama'] }}" allowfullscreen></iframe>
                    </div>
                @endif
            </section>
        @endforeach
    </div>

    <aside>
        <section class="card card-pad space-y-3">
            <h2 class="h3">Mau minta masukan?</h2>
            <p class="text-sm text-muted">Buka salah satu beasiswa atau lomba, lalu pilih mentor di bagian "Minta masukan mentor". Mentor membantu secara sukarela.</p>
            <a href="/find-scholarship" class="btn btn-soft w-full">Cari Beasiswa</a>
        </section>
    </aside>
</div>
@endsection

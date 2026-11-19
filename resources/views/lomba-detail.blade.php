@extends('layouts.app')
@section('title', $a['nama'])

@section('content')
<a href="/lomba" class="link text-sm">&larr; Kembali ke daftar</a>

<section class="card card-pad flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-start gap-4">
        <span class="logo-tile !size-16 !text-lg {{ \App\Support\Palette::tile('c'.$a['id']) }}">{{ \App\Support\Palette::initial($a['nama']) }}</span>
        <div class="space-y-2">
            <h1 class="h2">{{ $a['nama'] }}</h1>
            @if ($a['tipe'])<p class="text-muted">{{ $a['tipe'] }}</p>@endif
            <div class="flex flex-wrap gap-1.5">
                @if ($a['kategori'])<span class="badge badge-blue">{{ $a['kategori'] }}</span>@endif
                @if ($a['mode'])<span class="badge badge-yellow">{{ $a['mode'] }}</span>@endif
                @if ($a['lokasi'])<span class="badge badge-gray">{{ $a['lokasi'] }}</span>@endif
            </div>
        </div>
    </div>
    <a href="{{ route('apply.go', ['type' => 'competition', 'id' => $a['id']]) }}" target="_blank" rel="noopener" class="btn btn-primary shrink-0">Buka situs resmi</a>
</section>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        @if ($a['description'])
            <section class="card card-pad space-y-2">
                <h2 class="h3">Tentang lomba ini</h2>
                <p class="text-muted whitespace-pre-line">{{ $a['description'] }}</p>
            </section>
        @endif
        @if ($a['benefits'])
            <section class="card card-pad space-y-2">
                <h2 class="h3">Manfaatnya</h2>
                <p class="text-muted whitespace-pre-line">{{ $a['benefits'] }}</p>
            </section>
        @endif
        @if ($a['youtube'])
            <section class="card card-pad space-y-3">
                <h2 class="h3">Video</h2>
                <div class="aspect-video overflow-hidden rounded-xl bg-slate-100">
                    <iframe class="size-full" loading="lazy" src="https://www.youtube-nocookie.com/embed/{{ $a['youtube'] }}" title="Video {{ $a['nama'] }}" allowfullscreen></iframe>
                </div>
            </section>
        @endif
        <section class="card card-pad">
            <h2 class="h3 mb-3">Info lomba</h2>
            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                @foreach ([['Lokasi', $a['lokasi']], ['Mode', $a['mode']], ['Tanggal', $a['founded']], ['Biaya', $a['biaya']], ['Telepon', $a['phone']], ['Email', $a['email']]] as [$label, $value])
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-muted">{{ $label }}</dt>
                        <dd class="font-semibold">{{ filled($value) ? $value : '-' }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    </div>

    <aside class="space-y-6">
        <section class="card card-pad space-y-3 !border-sun-400 !bg-sun-100/50">
            <h2 class="h3">Daftar di situs resmi</h2>
            <p class="text-sm text-muted">Pendaftaran, syarat, dan jadwal ada di situs penyelenggara. Cek di sana ya.</p>
            <a href="{{ route('apply.go', ['type' => 'competition', 'id' => $a['id']]) }}" target="_blank" rel="noopener" class="btn btn-accent w-full">Buka situs resmi &rarr;</a>
            @if ($a->last_checked_at)
                <p class="text-xs text-muted">Terakhir dicek: {{ $a->last_checked_at->translatedFormat('d F Y') }}</p>
            @endif
        </section>

        @include('partials.review-request-form', ['targetType' => 'competition', 'targetId' => $a['id']])
    </aside>
</div>
@endsection

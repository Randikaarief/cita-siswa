@extends('layouts.app')
@section('title', $s['nama'])

@section('content')
<a href="/find-scholarship" class="link text-sm">&larr; Kembali ke daftar</a>

<section class="card card-pad flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-start gap-4">
        <span class="logo-tile !size-16 !text-lg {{ \App\Support\Palette::tile($s['id']) }}">{{ $s['logo_text'] ?: \App\Support\Palette::initial($s['nama']) }}</span>
        <div class="space-y-2">
            <h1 class="h2">{{ $s['nama'] }}</h1>
            @if ($s['sub'])<p class="text-muted">{{ $s['sub'] }}</p>@endif
            <div class="flex flex-wrap gap-1.5">
                @if ($s['kategori'])<span class="badge badge-blue">{{ $s['kategori'] }}</span>@endif
                @if ($s['tipe'])<span class="badge badge-yellow">{{ $s['tipe'] }}</span>@endif
                @if ($s['level'])<span class="badge badge-gray">{{ $s['level'] }}</span>@endif
                @if ($s['lokasi'])<span class="badge badge-gray">{{ $s['lokasi'] }}</span>@endif
            </div>
        </div>
    </div>
    <a href="{{ route('apply.go', ['type' => 'scholarship', 'id' => $s['id']]) }}" target="_blank" rel="noopener" class="btn btn-primary shrink-0">Buka situs resmi</a>
</section>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        @if ($s['desc'])
            <section class="card card-pad space-y-2">
                <h2 class="h3">Tentang beasiswa ini</h2>
                <p class="text-muted whitespace-pre-line">{{ $s['desc'] }}</p>
            </section>
        @endif

        @if (filled($s['requirements']))
            <section class="card card-pad space-y-3">
                <h2 class="h3">Persyaratan umum</h2>
                <ul class="space-y-2">
                    @foreach (array_filter(array_map('trim', explode(',', (string) $s['requirements']))) as $req)
                        <li class="flex gap-2.5 text-sm"><span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-sun-400 text-xs font-extrabold">&#10003;</span>{{ $req }}</li>
                    @endforeach
                </ul>
                <p class="text-xs text-muted">Ini ringkasan umum. Syarat lengkap dan terbaru ada di situs resmi penyelenggara.</p>
            </section>
        @endif

        <section class="card card-pad">
            <h2 class="h3 mb-3">Info penyelenggara</h2>
            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                @foreach ([['Lokasi', $s['lokasi']], ['Jenjang', $s['level']], ['Deadline', $s['deadline']], ['Jenis lembaga', $s['org_type']], ['Berdiri', $s['founded']], ['Ukuran', $s['size']], ['Telepon', $s['phone']], ['Email', $s['email']]] as [$label, $value])
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
            <a href="{{ route('apply.go', ['type' => 'scholarship', 'id' => $s['id']]) }}" target="_blank" rel="noopener" class="btn btn-accent w-full">Buka situs resmi &rarr;</a>
            @if ($s->last_checked_at)
                <p class="text-xs text-muted">Terakhir dicek: {{ $s->last_checked_at->translatedFormat('d F Y') }}</p>
            @endif
        </section>

        @include('partials.review-request-form', ['targetType' => 'scholarship', 'targetId' => $s['id']])
    </aside>
</div>
@endsection

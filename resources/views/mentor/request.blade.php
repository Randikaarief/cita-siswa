@extends('panel.layout')
@section('title', 'Pengajuan')

@php($target = $r->scholarship ?? $r->competition)

@section('content')
<a href="{{ route('mentor.requests') }}" class="link text-sm">&larr; Kembali ke daftar</a>

<section class="card card-pad space-y-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="eyebrow">Pengajuan masukan</p>
            <h1 class="h2">{{ $r->user?->name ?? '-' }}</h1>
            <p class="text-muted">untuk <span class="font-bold text-ink">{{ $target?->nama ?? '-' }}</span> &middot; {{ $r->created_at->translatedFormat('d M Y, H:i') }}</p>
        </div>
        <span class="badge {{ $r->statusBadge() }}">{{ $r->statusLabel() }}</span>
    </div>

    @if ($r->note)
        <div class="rounded-xl bg-brand-50 px-4 py-3 text-sm">
            <p class="text-xs font-extrabold uppercase tracking-wide text-brand-500">Catatan siswa</p>
            <p class="mt-1 whitespace-pre-line">{{ $r->note }}</p>
        </div>
    @endif

    <div class="space-y-2">
        <h2 class="h3">Berkas yang dibagikan</h2>
        @forelse ($r->submissions as $file)
            <div class="flex flex-wrap items-center gap-3 rounded-xl border border-line px-4 py-3 text-sm">
                <span class="badge {{ $file->type === 'essay' ? 'badge-yellow' : 'badge-blue' }}">{{ $file->type === 'essay' ? 'Esai' : 'CV' }}</span>
                <span class="min-w-0 flex-1 truncate font-bold">{{ $file->title }}</span>
                <span class="text-xs text-muted">{{ $file->sizeLabel() }}</span>
                @if ($r->isOpen())
                    <a href="{{ route('mentor.files', $file) }}" target="_blank" class="btn btn-soft btn-sm">Buka PDF</a>
                @endif
            </div>
        @empty
            <p class="text-sm text-muted">Tidak ada berkas.</p>
        @endforelse
        @unless ($r->isOpen())
            <p class="help">Akses ke berkas sudah ditutup karena pengajuan ini tidak lagi terbuka.</p>
        @endunless
    </div>
</section>

@if ($r->isOpen())
    <section class="card card-pad space-y-4">
        <h2 class="h3">Tulis masukanmu</h2>
        <form action="{{ route('mentor.requests.reply', $r) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label for="feedback" class="label">Masukan untuk siswa</label>
                <textarea id="feedback" name="feedback" rows="7" maxlength="2000" required class="textarea" placeholder="Tulis masukan yang jelas dan menyemangati.">{{ old('feedback') }}</textarea>
                <p class="help">Maksimal 2.000 karakter. Setelah terkirim, akses ke berkas siswa ditutup.</p>
            </div>
            <button type="submit" class="btn btn-primary">Kirim masukan</button>
        </form>

        <div class="border-t border-line pt-4">
            <h3 class="font-extrabold">Belum bisa membantu?</h3>
            <p class="help">Tidak apa-apa. Siswa akan diberi tahu dan bisa mencoba mentor lain.</p>
            <form action="{{ route('mentor.requests.decline', $r) }}" method="POST" class="mt-3 flex flex-wrap items-end gap-3" onsubmit="return confirm('Tolak pengajuan ini?')">
                @csrf
                <div class="min-w-64 flex-1">
                    <label for="reason" class="label">Alasan (opsional)</label>
                    <input id="reason" name="reason" maxlength="255" class="input" placeholder="Contoh: sedang sibuk ujian">
                </div>
                <button type="submit" class="btn btn-line">Belum bisa membantu</button>
            </form>
        </div>
    </section>
@elseif ($r->feedback)
    <section class="card card-pad space-y-2">
        <h2 class="h3">Masukan yang kamu kirim</h2>
        <p class="whitespace-pre-line text-muted">{{ $r->feedback }}</p>
    </section>
@elseif ($r->decline_reason)
    <section class="card card-pad space-y-2">
        <h2 class="h3">Alasanmu</h2>
        <p class="text-muted">{{ $r->decline_reason }}</p>
    </section>
@endif
@endsection

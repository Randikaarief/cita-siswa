@extends('panel.layout')
@section('title', 'Corong')

@php
    $pct = fn ($v) => $v === null ? '-' : number_format($v, 1, ',', '.').'%';
@endphp

@section('content')
<div class="space-y-1">
    <h1 class="h2">Corong pemasaran</h1>
    <p class="text-sm text-muted">Dari klik link Instagram sampai siswa mengunggah berkas. Hanya angka agregat, tanpa data pribadi.</p>
</div>

<div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
    @foreach ([['Klik link', $totals['clicks']], ['Pendaftaran', $totals['registrations']], ['Mengunggah berkas', $totals['uploaders']], ['Diteruskan ke situs resmi', $totals['forwarded']], ['ACR', $pct($totals['acr'])]] as [$label, $value])
        <div class="card card-pad">
            <p class="text-xs font-bold text-muted">{{ $label }}</p>
            <p class="text-3xl font-extrabold">{{ $value }}</p>
        </div>
    @endforeach
</div>

<div class="notice notice-info space-y-1 text-xs font-semibold leading-relaxed">
    <p><b>ACR</b> = siswa yang mengunggah berkas &divide; klik link &times; 100%. <b>CTR</b> = klik &divide; impresi &times; 100% (isi impresi di tabel).</p>
    <p><b>Pendaftaran</b> = akun siswa dengan email yang sudah terverifikasi. Akun yang belum klik link di emailnya tidak dihitung.</p>
    <p>Hanya pendaftar yang datang lewat link kampanye yang masuk tabel. Total siswa terdaftar semuanya: {{ $allStudents }}.</p>
</div>

<div class="tbl-wrap">
    <table class="tbl">
        <thead>
            <tr>
                <th>Kampanye</th>
                <th>Sumber</th>
                <th>Impresi</th>
                <th>Klik</th>
                <th>CTR</th>
                <th>Daftar</th>
                <th>Unggah</th>
                <th>ACR</th>
                <th>Ke situs resmi</th>
                <th>Minta masukan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="font-bold">{{ $row['campaign'] !== '' ? $row['campaign'] : '(tanpa nama kampanye)' }}</td>
                    <td class="text-muted">{{ $row['source'] ?? '-' }}{{ $row['medium'] ? ' / '.$row['medium'] : '' }}</td>
                    <td>
                        @if ($row['campaign'] !== '')
                            <form method="POST" action="{{ route('admin.funnel.impressions') }}" class="flex items-center gap-1.5">
                                @csrf
                                <input type="hidden" name="campaign" value="{{ $row['campaign'] }}">
                                <input type="number" name="impressions" min="0" value="{{ $row['impressions'] }}" placeholder="isi" class="input !w-24 !py-1.5">
                                <button type="submit" class="btn btn-soft btn-sm">Simpan</button>
                            </form>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $row['clicks'] }}</td>
                    <td>{{ $pct($row['ctr']) }}</td>
                    <td>{{ $row['registrations'] }}</td>
                    <td>{{ $row['uploaders'] }}</td>
                    <td class="font-bold">{{ $pct($row['acr']) }}</td>
                    <td>{{ $row['forwarded'] }}</td>
                    <td>{{ $row['requested'] }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted">Belum ada kunjungan dari link kampanye. Buat link di bawah dan taruh di bio Instagram.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<section id="utm-builder" class="card card-pad space-y-4">
    <div>
        <h2 class="h3">Pembuat link UTM</h2>
        <p class="help">Taruh link hasilnya di bio atau deskripsi Instagram. Setiap video sebaiknya punya nama kampanye sendiri.</p>
    </div>
    <div class="grid gap-3 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="label" for="utm-base">Alamat website</label>
            <input id="utm-base" name="base" class="input" value="{{ url('/') }}">
        </div>
        <div>
            <label class="label" for="utm-source">Sumber (utm_source)</label>
            <input id="utm-source" name="utm_source" class="input" value="instagram">
        </div>
        <div>
            <label class="label" for="utm-medium">Medium (utm_medium)</label>
            <input id="utm-medium" name="utm_medium" class="input" value="reels">
        </div>
        <div class="sm:col-span-2">
            <label class="label" for="utm-campaign">Nama kampanye (utm_campaign)</label>
            <input id="utm-campaign" name="utm_campaign" class="input" placeholder="contoh: podcast-eps1">
        </div>
    </div>
    <div class="flex flex-wrap gap-2">
        <input readonly data-utm-output class="input flex-1 min-w-64" aria-label="Link hasil">
        <button type="button" data-utm-copy class="btn btn-primary">Salin</button>
    </div>
</section>

<section class="card card-pad space-y-3">
    <h2 class="h3">Pengajuan masukan ke mentor</h2>
    <p class="help">Mentor membantu secara sukarela, jadi tidak semua pengajuan dibalas.</p>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        @foreach ($statusCounts as $label => $count)
            <div class="rounded-xl bg-brand-50 px-4 py-3">
                <p class="text-xs font-bold text-muted">{{ $label }}</p>
                <p class="text-2xl font-extrabold">{{ $count }}</p>
            </div>
        @endforeach
    </div>
</section>
@endsection

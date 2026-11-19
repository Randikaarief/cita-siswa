@extends('panel.layout')
@section('title', 'Data contoh')

@section('content')
<div class="max-w-xl space-y-4">
    <h1 class="h2">Data contoh</h1>
    <p class="text-sm text-muted">Data contoh (bertanda "Contoh") ada supaya tampilan tidak kosong. Sembunyikan atau hapus sebelum website dibuka untuk siswa.</p>

    <div class="card divide-y divide-line text-sm">
        @foreach ($counts as $label => $count)
            <div class="flex justify-between px-5 py-3"><span>{{ $label }}</span><span class="font-semibold">{{ $count }}</span></div>
        @endforeach
    </div>

    <form action="{{ route('admin.samples.hide') }}" method="POST" class="card card-pad space-y-2">
        @csrf
        <h2 class="h3">Sembunyikan</h2>
        <p class="text-sm text-muted">Data tidak tampil ke siswa tapi masih tersimpan.</p>
        <button type="submit" class="btn btn-soft btn-sm">Sembunyikan semua data contoh</button>
    </form>

    <form action="{{ route('admin.samples.purge') }}" method="POST" class="card card-pad space-y-3 !border-rose-200">
        @csrf
        <h2 class="h3 text-rose-700">Hapus permanen</h2>
        <p class="text-sm text-muted">Pengajuan dan riwayat klik yang terkait ikut terhapus. Tidak bisa dibatalkan.</p>
        @include('panel._field', ['name' => 'confirm', 'label' => 'Ketik HAPUS untuk memastikan'])
        <button type="submit" class="btn btn-danger">Hapus semua data contoh</button>
    </form>
</div>
@endsection

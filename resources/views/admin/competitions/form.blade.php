@extends('panel.layout')
@section('title', $item->exists ? 'Edit lomba' : 'Tambah lomba')

@section('content')
<div class="max-w-4xl space-y-4">
    <h1 class="h2">{{ $item->exists ? 'Edit lomba' : 'Tambah lomba' }}</h1>

    <form action="{{ $item->exists ? route('admin.competitions.update', $item->id) : route('admin.competitions.store') }}" method="POST" class="card card-pad space-y-4">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <div class="grid sm:grid-cols-2 gap-4">
            @include('panel._field', ['name' => 'nama', 'label' => 'Nama lomba', 'value' => $item->nama, 'required' => true])
            @include('panel._field', ['name' => 'tipe', 'label' => 'Jenis lomba (akademik atau non-akademik)', 'value' => $item->tipe])
            @include('panel._field', ['name' => 'website', 'label' => 'Link resmi penyelenggara', 'value' => $item->website, 'placeholder' => 'https://...', 'help' => 'Tombol Apply mengarah ke sini. Pendaftaran tetap di situs resmi.'])
            @include('panel._field', ['name' => 'last_checked_at', 'label' => 'Terakhir dicek ke situs resmi', 'type' => 'date', 'value' => $item->last_checked_at?->format('Y-m-d')])
            @include('panel._field', ['name' => 'kategori', 'label' => 'Kategori', 'value' => $item->kategori, 'placeholder' => 'Pemerintah / Private Company / Lembaga Internasional'])
            @include('panel._field', ['name' => 'lokasi', 'label' => 'Lokasi', 'value' => $item->lokasi])
            @include('panel._field', ['name' => 'mode', 'label' => 'Mode', 'value' => $item->mode, 'placeholder' => 'ONLINE atau OFFLINE'])
            @include('panel._field', ['name' => 'beasiswa', 'label' => 'Hadiah atau program terkait', 'value' => $item->beasiswa])
            @include('panel._field', ['name' => 'founded', 'label' => 'Tanggal', 'value' => $item->founded])
            @include('panel._field', ['name' => 'biaya', 'label' => 'Biaya', 'value' => $item->biaya, 'help' => 'Jangan menulis "gratis" kecuali sudah dipastikan di situs resmi.'])
            @include('panel._field', ['name' => 'phone', 'label' => 'Telepon', 'value' => $item->phone])
            @include('panel._field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $item->email])
            @include('panel._field', ['name' => 'avatar_color', 'label' => 'Warna logo (class Tailwind)', 'value' => $item->avatar_color, 'placeholder' => 'bg-[#E94D74]'])
            @include('panel._field', ['name' => 'youtube', 'label' => 'Video (link YouTube)', 'value' => $item->youtube ? 'https://youtu.be/'.$item->youtube : '', 'placeholder' => 'https://youtu.be/...'])
        </div>
        @include('panel._field', ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'value' => $item->description])
        @include('panel._field', ['name' => 'benefits', 'label' => 'Manfaat', 'type' => 'textarea', 'value' => $item->benefits])

        <div class="space-y-3 pt-2">
            @include('panel._check', ['name' => 'is_visible', 'label' => 'Tampil ke siswa', 'checked' => $item->is_visible])
            @include('panel._check', ['name' => 'is_sample', 'label' => 'Tandai sebagai data contoh', 'checked' => $item->is_sample, 'help' => 'Hilangkan centang kalau isinya sudah data asli.'])
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.competitions.index') }}" class="btn btn-line">Batal</a>
        </div>
    </form>
</div>
@endsection

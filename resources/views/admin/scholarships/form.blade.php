@extends('panel.layout')
@section('title', $item->exists ? 'Edit beasiswa' : 'Tambah beasiswa')

@section('content')
<div class="max-w-4xl space-y-4">
    <h1 class="h2">{{ $item->exists ? 'Edit beasiswa' : 'Tambah beasiswa' }}</h1>

    <form action="{{ $item->exists ? route('admin.scholarships.update', $item->id) : route('admin.scholarships.store') }}" method="POST" class="card card-pad space-y-4">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <div class="grid sm:grid-cols-2 gap-4">
            @include('panel._field', ['name' => 'nama', 'label' => 'Nama beasiswa', 'value' => $item->nama, 'required' => true])
            @include('panel._field', ['name' => 'sub', 'label' => 'Sub judul', 'value' => $item->sub])
            @include('panel._field', ['name' => 'website', 'label' => 'Link resmi penyelenggara', 'value' => $item->website, 'placeholder' => 'https://...', 'help' => 'Tombol Apply Now mengarah ke sini. Pendaftaran tetap di situs resmi.'])
            @include('panel._field', ['name' => 'last_checked_at', 'label' => 'Terakhir dicek ke situs resmi', 'type' => 'date', 'value' => $item->last_checked_at?->format('Y-m-d')])
            @include('panel._field', ['name' => 'kategori', 'label' => 'Kategori', 'value' => $item->kategori, 'placeholder' => 'Pemerintah / Private Company / Lembaga Internasional', 'help' => 'Dipakai filter di halaman Find scholarship.'])
            @include('panel._field', ['name' => 'tipe', 'label' => 'Tipe', 'value' => $item->tipe, 'help' => 'Jangan menulis "gratis" atau janji lolos kecuali sudah dipastikan di situs resmi.'])
            @include('panel._field', ['name' => 'lokasi', 'label' => 'Lokasi', 'value' => $item->lokasi])
            @include('panel._field', ['name' => 'level', 'label' => 'Jenjang', 'value' => $item->level])
            @include('panel._field', ['name' => 'deadline', 'label' => 'Deadline', 'value' => $item->deadline, 'help' => 'Tulis sebagai teks, atau kosongkan kalau belum pasti. Arahkan siswa cek jadwal di situs resmi.'])
            @include('panel._field', ['name' => 'posted', 'label' => 'Tanggal diposting', 'value' => $item->posted])
            @include('panel._field', ['name' => 'founded', 'label' => 'Berdiri', 'value' => $item->founded])
            @include('panel._field', ['name' => 'org_type', 'label' => 'Jenis lembaga', 'value' => $item->org_type])
            @include('panel._field', ['name' => 'size', 'label' => 'Ukuran lembaga', 'value' => $item->size])
            @include('panel._field', ['name' => 'phone', 'label' => 'Telepon', 'value' => $item->phone])
            @include('panel._field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $item->email])
            @include('panel._field', ['name' => 'logo_text', 'label' => 'Teks logo (1 sampai 3 huruf)', 'value' => $item->logo_text])
            @include('panel._field', ['name' => 'color', 'label' => 'Warna logo (class Tailwind)', 'value' => $item->color, 'placeholder' => 'bg-gradient-to-br from-blue-800 to-blue-600'])
        </div>
        @include('panel._field', ['name' => 'desc', 'label' => 'Deskripsi singkat', 'type' => 'textarea', 'value' => $item->desc])
        @include('panel._field', ['name' => 'requirements', 'label' => 'Persyaratan umum (pisahkan dengan koma)', 'type' => 'textarea', 'value' => $item->requirements, 'help' => 'Contoh: IPK minimal 3.00, aktif organisasi. Selalu arahkan ke sumber resmi untuk syarat lengkap.'])

        <div class="space-y-3 pt-2">
            @include('panel._check', ['name' => 'is_visible', 'label' => 'Tampil ke siswa', 'checked' => $item->is_visible])
            @include('panel._check', ['name' => 'is_sample', 'label' => 'Tandai sebagai data contoh', 'checked' => $item->is_sample, 'help' => 'Hilangkan centang kalau isinya sudah data asli.'])
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.scholarships.index') }}" class="btn btn-line">Batal</a>
        </div>
    </form>
</div>
@endsection

@extends('panel.layout')
@section('title', 'Profil mentor')

@section('content')
<div class="max-w-3xl space-y-4">
    <h1 class="h2">Profil mentor</h1>
    <p class="text-sm text-muted">Profil ini tampil di halaman Alumni. Nomor WhatsApp tidak tampil di sana. Nomor hanya terlihat oleh siswa yang mengajukan ke kamu.</p>

    <form action="{{ route('mentor.profile.update') }}" method="POST" class="card card-pad space-y-4">
        @csrf
        @method('PUT')

        @include('panel._check', ['name' => 'is_willing_to_help', 'label' => 'Saya bersedia membantu bedah berkas', 'checked' => $profile->is_willing_to_help, 'help' => 'Kalau dimatikan, namamu tidak muncul di pilihan mentor dan tombol WhatsApp hilang.'])

        <div class="grid sm:grid-cols-2 gap-4">
            @include('panel._field', ['name' => 'nama', 'label' => 'Nama', 'value' => $profile->nama, 'required' => true])
            @include('panel._field', ['name' => 'whatsapp', 'label' => 'Nomor WhatsApp', 'value' => $profile->whatsapp, 'placeholder' => '0812 3456 7890', 'help' => 'Isi supaya siswa bisa menghubungimu. Boleh dikosongkan.'])
            @include('panel._field', ['name' => 'tipe', 'label' => 'Alumni dari (beasiswa dan kampus)', 'value' => $profile->tipe])
            @include('panel._field', ['name' => 'beasiswa', 'label' => 'Beasiswa yang diterima', 'value' => $profile->beasiswa])
            @include('panel._field', ['name' => 'lokasi', 'label' => 'Lokasi', 'value' => $profile->lokasi])
            @include('panel._field', ['name' => 'max_active_requests', 'label' => 'Maksimal pengajuan aktif', 'type' => 'number', 'value' => $profile->max_active_requests, 'placeholder' => 'Kosongkan kalau tanpa batas', 'help' => 'Kalau sudah penuh, namamu tidak muncul di pilihan siswa. Isi 0 untuk berhenti sementara.'])
            @include('panel._field', ['name' => 'youtube', 'label' => 'Video (link YouTube)', 'value' => $profile->youtube ? 'https://youtu.be/'.$profile->youtube : '', 'placeholder' => 'https://youtu.be/...'])
        </div>
        @include('panel._field', ['name' => 'description', 'label' => 'Tentang saya', 'type' => 'textarea', 'value' => $profile->description])
        @include('panel._field', ['name' => 'benefits', 'label' => 'Yang bisa kubantu', 'type' => 'textarea', 'value' => $profile->benefits])

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection

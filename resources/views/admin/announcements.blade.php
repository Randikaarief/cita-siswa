@extends('panel.layout')
@section('title', 'Pengumuman')

@section('content')
<div class="max-w-xl space-y-4">
    <h1 class="h2">Pengumuman</h1>
    <p class="text-sm text-muted">Pengumuman muncul di halaman Alert siswa.</p>

    <form action="{{ route('admin.announcements.store') }}" method="POST" class="card card-pad space-y-4">
        @csrf
        @include('panel._field', ['name' => 'title', 'label' => 'Judul', 'required' => true])
        @include('panel._field', ['name' => 'subtitle', 'label' => 'Keterangan singkat (opsional)'])
        @include('panel._field', ['name' => 'badge', 'label' => 'Label kecil (opsional)'])
        @include('panel._field', ['name' => 'url', 'label' => 'Link (opsional)', 'placeholder' => '/find-scholarship atau https://...', 'help' => 'Halaman di website ini (diawali "/") atau alamat web resmi.'])

        <div class="space-y-2">
            <p class="label">Kirim ke</p>
            <label class="flex items-center gap-2 text-sm"><input type="radio" name="target" value="all" @checked(old('target', 'all') === 'all')> Semua siswa</label>
            <label class="flex items-center gap-2 text-sm"><input type="radio" name="target" value="email" @checked(old('target') === 'email')> Satu siswa (isi email di bawah)</label>
            @error('target')<p class="error">{{ $message }}</p>@enderror
        </div>
        @include('panel._field', ['name' => 'email', 'label' => 'Email siswa', 'type' => 'email'])

        <button type="submit" class="btn btn-primary">Kirim</button>
    </form>
</div>
@endsection

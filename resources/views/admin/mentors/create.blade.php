@extends('panel.layout')
@section('title', 'Buat akun mentor')

@section('content')
<div class="max-w-xl space-y-4">
    <h1 class="h2">Buat akun mentor</h1>
    <p class="text-sm text-muted">Akun mentor hanya dibuat admin. Berikan email dan password sementara ke mentor. Ia wajib menggantinya saat login pertama.</p>

    <form action="{{ route('admin.mentors.store') }}" method="POST" class="card card-pad space-y-4">
        @csrf
        @include('panel._field', ['name' => 'name', 'label' => 'Nama mentor', 'required' => true])
        @include('panel._field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true])
        @include('panel._field', ['name' => 'password', 'label' => 'Password sementara (minimal 8 karakter)', 'type' => 'text', 'required' => true])
        @include('panel._field', ['name' => 'beasiswa', 'label' => 'Beasiswa yang diterima'])
        @include('panel._field', ['name' => 'lokasi', 'label' => 'Lokasi'])
        @include('panel._select', ['name' => 'kategori', 'label' => 'Kategori', 'options' => $kategori])
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary">Buat akun</button>
            <a href="{{ route('admin.mentors.index') }}" class="btn btn-line">Batal</a>
        </div>
    </form>
</div>
@endsection

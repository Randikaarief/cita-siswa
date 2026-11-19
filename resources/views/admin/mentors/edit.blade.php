@extends('panel.layout')
@section('title', 'Edit mentor')

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="h2">Edit mentor</h1>
        <a href="{{ route('admin.mentors.index') }}" class="link text-sm">&larr; Kembali</a>
    </div>

    <form action="{{ route('admin.mentors.update', $item->id) }}" method="POST" class="card card-pad space-y-4">
        @csrf
        @method('PUT')
        <h2 class="h3">Profil</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            @include('panel._field', ['name' => 'nama', 'label' => 'Nama', 'value' => $item->nama, 'required' => true])
            @include('panel._field', ['name' => 'whatsapp', 'label' => 'Nomor WhatsApp', 'value' => $item->whatsapp, 'placeholder' => '0812 3456 7890', 'help' => 'Idealnya diisi mentor sendiri. Hanya terlihat oleh siswa yang mengajukan ke mentor ini.'])
            @include('panel._field', ['name' => 'tipe', 'label' => 'Alumni dari', 'value' => $item->tipe])
            @include('panel._field', ['name' => 'beasiswa', 'label' => 'Beasiswa yang diterima', 'value' => $item->beasiswa])
            @include('panel._field', ['name' => 'lokasi', 'label' => 'Lokasi', 'value' => $item->lokasi])
            @include('panel._field', ['name' => 'max_active_requests', 'label' => 'Maksimal pengajuan aktif', 'type' => 'number', 'value' => $item->max_active_requests, 'placeholder' => 'Kosongkan kalau tanpa batas'])
            @include('panel._select', ['name' => 'kategori', 'label' => 'Kategori', 'options' => $kategori, 'value' => $item->kategori])
            @include('panel._field', ['name' => 'youtube', 'label' => 'Video (link YouTube)', 'value' => $item->youtube ? 'https://youtu.be/'.$item->youtube : '', 'placeholder' => 'https://youtu.be/...', 'class' => 'sm:col-span-2'])
        </div>
        @include('panel._field', ['name' => 'description', 'label' => 'Tentang', 'type' => 'textarea', 'value' => $item->description])
        @include('panel._field', ['name' => 'benefits', 'label' => 'Yang bisa dibantu', 'type' => 'textarea', 'value' => $item->benefits])
        <div class="space-y-3 pt-2">
            @include('panel._check', ['name' => 'is_willing_to_help', 'label' => 'Bersedia membantu bedah berkas', 'checked' => $item->is_willing_to_help])
            @include('panel._check', ['name' => 'is_visible', 'label' => 'Tampil di halaman Alumni', 'checked' => $item->is_visible])
            @include('panel._check', ['name' => 'is_sample', 'label' => 'Tandai sebagai data contoh', 'checked' => $item->is_sample, 'help' => 'Hilangkan centang kalau isinya sudah data asli.'])
        </div>
        <button type="submit" class="btn btn-primary">Simpan profil</button>
    </form>

    <div class="card card-pad space-y-4">
        <h2 class="h3">Akun</h2>

        @if ($item->user)
            <p class="text-sm text-muted">Email login: <span class="font-semibold">{{ $item->user->email }}</span>
                @if (! $item->user->is_active)<span class="badge badge-red"> (dinonaktifkan)</span>@endif
            </p>

            <form action="{{ route('admin.mentors.reset', $item->id) }}" method="POST" class="flex flex-wrap items-end gap-3">
                @csrf
                @include('panel._field', ['name' => 'password', 'label' => 'Password sementara baru', 'class' => 'w-64'])
                <button type="submit" class="btn btn-soft btn-sm">Atur ulang password</button>
            </form>
        @else
            <p class="text-sm text-muted">Profil ini belum punya akun login (data contoh atau belum dibuatkan akun).</p>
        @endif

        <form action="{{ route('admin.mentors.toggle', $item->id) }}" method="POST" onsubmit="return confirm('Yakin?')">
            @csrf
            @php($active = $item->user ? $item->user->is_active : $item->is_visible)
            <button type="submit" class="{{ $active ? 'link-danger' : 'link' }}">
                {{ $active ? 'Nonaktifkan mentor' : 'Aktifkan kembali mentor' }}
            </button>
        </form>
    </div>
</div>
@endsection

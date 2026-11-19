@extends('panel.layout')
@section('title', 'Ganti password')

@section('content')
<div class="max-w-md space-y-4">
    <h1 class="h2">Ganti password</h1>
    @if ($user->must_change_password)
        <p class="text-sm text-muted">Password kamu masih sementara. Ganti dulu sebelum lanjut.</p>
    @endif
    <form action="{{ route('panel.password.update') }}" method="POST" class="card card-pad space-y-4">
        @csrf
        @method('PUT')
        @include('panel._field', ['name' => 'current_password', 'label' => 'Password saat ini', 'type' => 'password', 'required' => true])
        @include('panel._field', ['name' => 'password', 'label' => 'Password baru (minimal 8 karakter)', 'type' => 'password', 'required' => true])
        @include('panel._field', ['name' => 'password_confirmation', 'label' => 'Ulangi password baru', 'type' => 'password', 'required' => true])
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection

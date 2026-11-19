@extends('layouts.guest')
@section('title', 'Masuk')

@php($asAdmin = ($sebagai ?? 'siswa') === 'admin')

@section('content')
{{-- Dua pilihan masuk --}}
<div class="grid grid-cols-2 gap-2" role="tablist" aria-label="Masuk sebagai">
    <a href="{{ route('login') }}" role="tab" aria-selected="{{ $asAdmin ? 'false' : 'true' }}"
       class="btn {{ $asAdmin ? 'btn-soft' : 'btn-primary' }}">Siswa</a>
    <a href="{{ route('login', ['sebagai' => 'admin']) }}" role="tab" aria-selected="{{ $asAdmin ? 'true' : 'false' }}"
       class="btn {{ $asAdmin ? 'btn-primary' : 'btn-soft' }}">Admin</a>
</div>

<div class="space-y-1">
    <h1 class="h2">{{ $asAdmin ? 'Masuk sebagai Admin' : 'Masuk sebagai Siswa' }}</h1>
    <p class="text-sm text-muted">
        {{ $asAdmin ? 'Khusus pengelola website CITA SISWA.' : 'Kakak alumni mentor juga masuk lewat sini.' }}
    </p>
</div>
@include('partials.flash')

<form action="{{ route('login') }}" method="POST" class="space-y-4">
    @csrf
    <input type="hidden" name="sebagai" value="{{ $asAdmin ? 'admin' : 'siswa' }}">
    <div>
        <label for="email" class="label">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="input">
    </div>
    <div>
        <label for="password" class="label">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password" class="input">
    </div>
    <label class="flex items-center gap-2 text-sm text-muted">
        <input type="checkbox" name="remember" value="1" class="size-4 rounded border-line"> Ingat saya di perangkat ini
    </label>
    <button type="submit" class="btn btn-primary w-full">Masuk</button>
</form>

<p class="text-center text-sm"><a href="{{ route('password.request') }}" class="link">Lupa password?</a></p>
@unless ($asAdmin)
<p class="text-center text-sm text-muted">
    Belum punya akun? <a href="{{ route('register') }}" class="link">Daftar</a>
</p>
@endunless
@endsection

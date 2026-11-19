@extends('layouts.guest')
@section('title', 'Daftar')

@section('content')
<h1 class="h2">Daftar</h1>
@include('partials.flash')

<form action="{{ route('register') }}" method="POST" class="space-y-4">
    @csrf
    <div>
        <label for="name" class="label">Nama</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" class="input">
    </div>
    <div>
        <label for="email" class="label">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="input">
    </div>
    <div>
        <label for="password" class="label">Password</label>
        <input id="password" name="password" type="password" required autocomplete="new-password" class="input">
        <p class="help">Minimal 8 karakter. Catat baik-baik ya.</p>
    </div>
    <div>
        <label for="password_confirmation" class="label">Ulangi password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
    </div>
    <button type="submit" class="btn btn-primary w-full">Daftar</button>
</form>

<p class="text-center text-sm text-muted">
    Sudah punya akun? <a href="{{ route('login') }}" class="link">Masuk</a>
</p>
@endsection

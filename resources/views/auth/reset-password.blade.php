@extends('layouts.guest')
@section('title', 'Password baru')

@section('content')
<div class="space-y-1">
    <h1 class="h2">Buat password baru</h1>
    <p class="text-sm text-muted">Minimal 8 karakter. Ketik dua kali supaya tidak salah.</p>
</div>
@include('partials.flash')

<form action="{{ route('password.update') }}" method="POST" class="space-y-4">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div>
        <label for="email" class="label">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email" class="input">
    </div>
    <div>
        <label for="password" class="label">Password baru</label>
        <input id="password" name="password" type="password" required autofocus autocomplete="new-password" class="input">
    </div>
    <div>
        <label for="password_confirmation" class="label">Ulangi password baru</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
    </div>
    <button type="submit" class="btn btn-primary w-full">Simpan password</button>
</form>
@endsection

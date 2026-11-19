@extends('layouts.guest')
@section('title', 'Lupa password')

@section('content')
<div class="space-y-1">
    <h1 class="h2">Lupa password?</h1>
    <p class="text-sm text-muted">Tenang. Isi emailmu, nanti kami kirim link untuk membuat password baru.</p>
</div>
@include('partials.flash')

<form action="{{ route('password.email') }}" method="POST" class="space-y-4">
    @csrf
    <div>
        <label for="email" class="label">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="input">
    </div>
    <button type="submit" class="btn btn-primary w-full">Kirim link</button>
</form>

<p class="text-center text-sm text-muted">
    Ingat passwordnya? <a href="{{ route('login') }}" class="link">Masuk</a>
</p>
@endsection

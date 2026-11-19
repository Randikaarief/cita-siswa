@extends('layouts.app')
@section('title', 'Cek emailmu')

@section('content')
<section class="mx-auto w-full max-w-xl space-y-5">
    <div class="card card-pad space-y-4 text-center">
        <img src="{{ asset('images/logo.png') }}" alt="" width="360" height="296" class="mx-auto h-24 w-auto">
        <h1 class="h2">Cek emailmu, ya</h1>
        <p class="text-muted">
            Kami sudah mengirim link verifikasi ke <b class="text-ink">{{ auth()->user()->email }}</b>.
            Klik link itu supaya kamu bisa mengunggah berkas dan meminta masukan mentor.
        </p>
        <p class="text-sm text-muted">Belum ada? Cek folder spam. Link berlaku sekitar satu jam.</p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary w-full sm:w-auto">Kirim ulang email</button>
        </form>
    </div>

    <div class="notice notice-info space-y-1 text-sm">
        <p>Belum verifikasi pun kamu tetap bisa melihat beasiswa, lomba, dan alumni.</p>
        <p>
            <a href="/find-scholarship" class="link">Lihat-lihat dulu</a>
            &middot;
            <a href="/settings/account" class="link">Salah email? Ubah di Pengaturan</a>
        </p>
    </div>
</section>
@endsection

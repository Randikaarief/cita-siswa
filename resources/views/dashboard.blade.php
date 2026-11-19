@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="grid gap-6 md:grid-cols-4">
    <div>@include('partials.account-nav')</div>

    <div class="space-y-6 md:col-span-3">
        <div class="space-y-1">
            <p class="eyebrow">Dashboard</p>
            <h1 class="h1">Halo, {{ $user->name }}</h1>
            <p class="text-muted">Semangat ya. Satu langkah kecil hari ini sudah cukup.</p>
        </div>

        @unless ($user->profileIsComplete())
            <div class="notice notice-warn flex flex-wrap items-center justify-between gap-3">
                <span>Profilmu belum lengkap. Lengkapi supaya lebih mudah dikenali mentor.</span>
                <a href="/settings/personal" class="btn btn-accent btn-sm">Lengkapi profil</a>
            </div>
        @endunless

        <div class="grid gap-4 sm:grid-cols-3">
            <a href="/riwayat" class="card card-hover card-pad">
                <p class="text-sm font-bold text-muted">Riwayat</p>
                <p class="text-3xl font-extrabold">{{ $appliedCount }}</p>
            </a>
            <a href="/settings/personal#berkas" class="card card-hover card-pad">
                <p class="text-sm font-bold text-muted">Berkas tersimpan</p>
                <p class="text-3xl font-extrabold">{{ $submissionCount }}</p>
            </a>
            <a href="/alert" class="card card-hover card-pad">
                <p class="text-sm font-bold text-muted">Notifikasi baru</p>
                <p class="text-3xl font-extrabold">{{ $unreadAlerts }}</p>
            </a>
        </div>

        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="h3">Terakhir dibuka</h2>
                <a href="/riwayat" class="link text-sm">Lihat semua</a>
            </div>
            @forelse ($recent as $row)
                @include('partials.riwayat-row', ['row' => $row])
            @empty
                <div class="card card-pad space-y-3 text-center">
                    <p class="text-muted">Belum ada yang dibuka. Mulai dari daftar beasiswa.</p>
                    <a href="/find-scholarship" class="btn btn-primary mx-auto">Cari Beasiswa</a>
                </div>
            @endforelse
        </section>
    </div>
</div>
@endsection

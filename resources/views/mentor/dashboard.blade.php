@extends('panel.layout')
@section('title', 'Dashboard mentor')

@section('content')
<h1 class="h2">Halo, {{ auth()->user()->name }}</h1>

@if ($profile && ! $profile->is_willing_to_help)
    <div class="notice notice-warn">
        Saklar "bersedia membantu" masih mati, jadi siswa belum bisa mengajukan ke kamu.
        <a href="{{ route('mentor.profile') }}" class="font-extrabold underline">Atur di profil</a>
    </div>
@endif

<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    @foreach ([['Menunggu dibalas', $pending], ['Sudah dibalas', $replied], ['Belum bisa membantu', $declined]] as [$label, $count])
        <div class="card card-pad">
            <p class="text-sm font-bold text-muted">{{ $label }}</p>
            <p class="text-3xl font-extrabold">{{ $count }}</p>
        </div>
    @endforeach
</div>

<div class="space-y-3">
    <div class="flex items-center justify-between">
        <h2 class="h3">Pengajuan yang menunggu</h2>
        <a href="{{ route('mentor.requests') }}" class="link text-sm">Lihat semua &rarr;</a>
    </div>
    @include('panel._requests_table', ['requests' => $recent])
</div>
@endsection

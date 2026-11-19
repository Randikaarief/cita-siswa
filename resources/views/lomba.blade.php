@extends('layouts.app')
@section('title', 'Lomba')

@section('content')
<div class="space-y-1">
    <p class="eyebrow">Lomba</p>
    <h1 class="h1">Lomba untuk memperkuat portofolio</h1>
    <p class="text-muted">Lomba akademik maupun non-akademik bisa jadi nilai tambah saat melamar beasiswa.</p>
</div>

@include('partials.filter-bar', ['action' => '/lomba', 'placeholder' => 'Nama atau jenis lomba'])

@if ($competitions->isEmpty())
    <div class="card card-pad text-center text-muted">Tidak ada lomba yang cocok. Coba kata kunci lain.</div>
@else
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($competitions as $c)
            @include('partials.competition-card', ['c' => $c])
        @endforeach
    </div>
    <p class="text-xs text-muted">Jadwal dan syarat bisa berubah. Selalu cek di situs resmi penyelenggara.</p>
@endif
@endsection

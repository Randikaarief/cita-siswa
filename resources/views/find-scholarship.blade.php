@extends('layouts.app')
@section('title', 'Cari Beasiswa')

@section('content')
<div class="space-y-1">
    <p class="eyebrow">Beasiswa</p>
    <h1 class="h1">Cari beasiswa yang cocok</h1>
    <p class="text-muted">Klik satu beasiswa untuk melihat ringkasan dan link ke situs resminya.</p>
</div>

@include('partials.filter-bar', ['action' => '/find-scholarship', 'placeholder' => 'Nama beasiswa atau penyelenggara'])

@if ($scholarships->isEmpty())
    <div class="card card-pad text-center text-muted">Tidak ada beasiswa yang cocok. Coba kata kunci lain.</div>
@else
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($scholarships as $s)
            @include('partials.scholarship-card', ['s' => $s])
        @endforeach
    </div>
    <p class="text-xs text-muted">Jadwal dan syarat bisa berubah. Selalu cek di situs resmi penyelenggara.</p>
@endif
@endsection

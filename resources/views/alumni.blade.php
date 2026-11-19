@extends('layouts.app')
@section('title', 'Alumni')

@section('content')
<div class="space-y-1">
    <p class="eyebrow">Alumni</p>
    <h1 class="h1">Kenalan dengan kakak alumni</h1>
    <p class="text-muted">Mereka pernah ada di posisimu. Baca ceritanya, siapa tahu ada yang mirip denganmu.</p>
</div>

@include('partials.filter-bar', ['action' => '/alumni', 'placeholder' => 'Nama alumni atau beasiswa'])

@if ($alumni->isEmpty())
    <div class="card card-pad text-center text-muted">Tidak ada alumni yang cocok. Coba kata kunci lain.</div>
@else
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($alumni as $m)
            @include('partials.mentor-card', ['m' => $m])
        @endforeach
    </div>
@endif
@endsection

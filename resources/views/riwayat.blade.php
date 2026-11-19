@extends('layouts.app')
@section('title', 'Riwayat')

@section('content')
<div class="grid gap-6 md:grid-cols-4">
    <div>@include('partials.account-nav')</div>

    <div class="space-y-5 md:col-span-3">
        <div class="space-y-1">
            <h1 class="h2">Riwayat <span class="font-semibold text-muted">({{ $appliedCount }})</span></h1>
            <p class="text-sm text-muted">
                Beasiswa dan lomba yang pernah kamu buka, serta pengajuan masukan ke mentor.
                Status "Dibuka" artinya kamu membuka situs resminya, bukan bukti sudah mendaftar.
            </p>
        </div>

        @forelse ($rows as $row)
            @include('partials.riwayat-row', ['row' => $row])
        @empty
            <div class="card card-pad space-y-3 text-center">
                <p class="text-muted">Belum ada riwayat.</p>
                <a href="/find-scholarship" class="btn btn-primary mx-auto">Cari Beasiswa</a>
            </div>
        @endforelse
    </div>
</div>
@endsection

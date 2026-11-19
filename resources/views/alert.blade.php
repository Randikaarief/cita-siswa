@extends('layouts.app')
@section('title', 'Notifikasi')

@section('content')
<div class="grid gap-6 md:grid-cols-4">
    <div>@include('partials.account-nav')</div>

    <div class="space-y-4 md:col-span-3">
        <h1 class="h2">Notifikasi <span class="font-semibold text-muted">({{ $newCount }} baru)</span></h1>

        @forelse ($notifications as $n)
            <article class="card card-pad flex flex-wrap items-center gap-3 {{ $n->read_at === null ? '!border-brand-200' : '' }}">
                <span class="logo-tile bg-sun-400 text-ink">{{ \App\Support\Palette::initial($n->data['title'] ?? '!') }}</span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="font-extrabold">{{ $n->data['title'] ?? '' }}</h2>
                        @if (! empty($n->data['badge']))<span class="badge badge-blue">{{ $n->data['badge'] }}</span>@endif
                    </div>
                    @if (! empty($n->data['subtitle']))<p class="text-sm text-muted">{{ $n->data['subtitle'] }}</p>@endif
                    <p class="text-xs text-muted">{{ $n->created_at->diffForHumans() }}</p>
                </div>
                @if (! empty($n->data['url']))
                    <a href="{{ $n->data['url'] }}" class="btn btn-soft btn-sm">Lihat</a>
                @endif
            </article>
        @empty
            <div class="card card-pad text-center text-muted">Belum ada notifikasi.</div>
        @endforelse
    </div>
</div>
@endsection

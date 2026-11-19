@extends('layouts.app')
@section('title', 'Pengaturan')

@section('content')
<div class="grid gap-6 md:grid-cols-4">
    <div>@include('partials.account-nav')</div>

    <div class="space-y-6 md:col-span-3">
        <h1 class="h2">Pengaturan</h1>
        @include('partials.settings-tabs')

        <form action="{{ route('settings.social.update') }}" method="POST" class="card card-pad space-y-4">
            @csrf
            @method('PUT')
            <h2 class="h3">Media sosial</h2>
            <p class="help">Opsional. Hanya terlihat olehmu.</p>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'twitter' => 'X (Twitter)', 'youtube' => 'YouTube'] as $key => $label)
                    @include('partials.settings-field', ['name' => $key, 'label' => $label, 'value' => $user->social_links[$key] ?? '', 'placeholder' => 'Link atau nama akun'])
                @endforeach
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Pengaturan')

@section('content')
<div class="grid gap-6 md:grid-cols-4">
    <div>@include('partials.account-nav')</div>

    <div class="space-y-6 md:col-span-3">
        <h1 class="h2">Pengaturan</h1>
        @include('partials.settings-tabs')

        <form action="{{ route('settings.profile.update') }}" method="POST" class="card card-pad space-y-4">
            @csrf
            @method('PUT')
            <h2 class="h3">Profil</h2>
            <p class="help">Semua isian opsional dan hanya terlihat olehmu.</p>
            <div class="grid gap-4 sm:grid-cols-2">
                @include('partials.settings-field', ['name' => 'birth_date', 'label' => 'Tanggal lahir', 'type' => 'date', 'value' => $user->birth_date?->format('Y-m-d'), 'autocomplete' => 'bday'])
                @include('partials.settings-select', ['name' => 'gender', 'label' => 'Jenis kelamin', 'value' => $user->gender, 'options' => [
                    'female' => 'Perempuan', 'male' => 'Laki-laki', 'other' => 'Tidak ingin menyebut']])
                @include('partials.settings-select', ['name' => 'education_level', 'label' => 'Jenjang yang dituju', 'value' => $user->education_level, 'options' => [
                    'diploma' => 'Diploma', 'bachelor' => 'S1', 'master' => 'S2']])
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>
@endsection

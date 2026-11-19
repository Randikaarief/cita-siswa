@extends('layouts.app')
@section('title', 'Pengaturan')

@section('content')
<div class="grid gap-6 md:grid-cols-4">
    <div>@include('partials.account-nav')</div>

    <div class="space-y-6 md:col-span-3">
        <h1 class="h2">Pengaturan</h1>
        @include('partials.settings-tabs')

        <form action="{{ route('settings.contact.update') }}" method="POST" class="card card-pad space-y-4">
            @csrf
            @method('PUT')
            <h2 class="h3">Kontak</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                @include('partials.settings-field', ['name' => 'email', 'label' => 'Email (untuk masuk)', 'type' => 'email', 'value' => $user->email, 'autocomplete' => 'email', 'class' => 'sm:col-span-2'])
                @include('partials.settings-field', ['name' => 'phone', 'label' => 'Nomor telepon', 'type' => 'tel', 'value' => $user->phone, 'autocomplete' => 'tel'])
                @include('partials.settings-field', ['name' => 'location', 'label' => 'Kota atau desa', 'value' => $user->location, 'placeholder' => 'Contoh: Desa Jarak, Kediri'])
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>

        <form action="{{ route('settings.password.update') }}" method="POST" class="card card-pad space-y-4">
            @csrf
            @method('PUT')
            <h2 class="h3">Ganti password</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="current_password" class="label">Password saat ini</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" class="input">
                </div>
                <div>
                    <label for="new_password" class="label">Password baru</label>
                    <input id="new_password" name="password" type="password" autocomplete="new-password" class="input">
                </div>
                <div>
                    <label for="confirm_password" class="label">Ulangi password baru</label>
                    <input id="confirm_password" name="password_confirmation" type="password" autocomplete="new-password" class="input">
                </div>
            </div>
            <p class="help">Minimal 8 karakter.</p>
            <button type="submit" class="btn btn-primary">Ganti password</button>
        </form>

        <section class="card card-pad space-y-3 !border-rose-200">
            <h2 class="h3 text-rose-700">Tutup akun</h2>
            <p class="text-sm text-muted">Akun, berkas, dan riwayatmu akan dihapus permanen. Tidak bisa dibatalkan.</p>
            <details>
                <summary class="link-danger inline-block">Saya ingin menutup akun</summary>
                <form method="POST" action="{{ route('settings.account.destroy') }}" class="mt-3 flex flex-wrap items-end gap-3" onsubmit="return confirm('Akun dan semua berkasmu akan dihapus permanen. Lanjutkan?')">
                    @csrf
                    @method('DELETE')
                    <div class="w-full sm:w-72">
                        <label for="delete_password" class="label">Masukkan password untuk memastikan</label>
                        <input id="delete_password" name="password" type="password" required autocomplete="current-password" class="input">
                    </div>
                    <button type="submit" class="btn btn-danger">Tutup akun</button>
                </form>
            </details>
        </section>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Pengaturan')

@section('content')
<div class="grid gap-6 md:grid-cols-4">
    <div>@include('partials.account-nav')</div>

    <div class="space-y-6 md:col-span-3">
        <h1 class="h2">Pengaturan</h1>
        @include('partials.settings-tabs')

        {{-- Data diri --}}
        <form action="{{ route('settings.personal.update') }}" method="POST" class="card card-pad space-y-4">
            @csrf
            @method('PUT')
            <h2 class="h3">Data diri</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                @include('partials.settings-field', ['name' => 'name', 'label' => 'Nama lengkap', 'value' => $user->name, 'autocomplete' => 'name'])
                @include('partials.settings-field', ['name' => 'headline', 'label' => 'Judul singkat', 'value' => $user->headline, 'placeholder' => 'Contoh: Siswa SMA yang ingin kuliah teknik'])
                @include('partials.settings-select', ['name' => 'experience', 'label' => 'Pengalaman', 'value' => $user->experience, 'options' => [
                    'none' => 'Belum ada pengalaman', 'organization' => 'Organisasi atau ekstrakurikuler', 'competition' => 'Lomba atau prestasi', 'work' => 'Kerja atau magang']])
                @include('partials.settings-select', ['name' => 'education', 'label' => 'Pendidikan saat ini', 'value' => $user->education, 'options' => [
                    'smp' => 'SMP', 'sma' => 'SMA / SMK', 'diploma' => 'Diploma', 'bachelor' => 'S1']])
                @include('partials.settings-field', ['name' => 'website', 'label' => 'Website pribadi (opsional)', 'value' => $user->website, 'placeholder' => 'contoh.com', 'class' => 'sm:col-span-2'])
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>

        {{-- Foto --}}
        <form action="{{ route('settings.photo.update') }}" method="POST" enctype="multipart/form-data" class="card card-pad space-y-4">
            @csrf
            <h2 class="h3">Foto profil</h2>
            <div class="flex flex-wrap items-center gap-4">
                @if ($user->photo_path)
                    <img src="{{ $user->avatarUrl() }}" alt="Foto profilmu" class="size-20 rounded-full object-cover">
                @else
                    <span class="avatar !size-20 !text-3xl">{{ \App\Support\Palette::initial($user->name) }}</span>
                @endif
                <div class="space-y-2">
                    <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" required class="block text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:font-bold file:text-brand-600">
                    <p class="help">JPG, PNG, atau WebP. Maksimal 5 MB. Hanya kamu yang bisa melihat fotomu.</p>
                </div>
            </div>
            <button type="submit" class="btn btn-soft">Simpan foto</button>
        </form>

        {{-- CV dan esai --}}
        <section id="berkas" class="card card-pad space-y-4 scroll-mt-24">
            <div>
                <h2 class="h3">CV dan esai</h2>
                <p class="help">PDF saja, maksimal 2 MB, maksimal {{ \App\Http\Controllers\SubmissionController::MAX_FILES }} berkas. Hanya kamu dan mentor yang kamu pilih saat minta masukan yang bisa membukanya.</p>
            </div>

            @forelse ($submissions as $submission)
                <div class="flex flex-wrap items-center gap-3 rounded-xl border border-line px-4 py-3">
                    <span class="badge {{ $submission->type === 'essay' ? 'badge-yellow' : 'badge-blue' }}">{{ $submission->type === 'essay' ? 'Esai' : 'CV' }}</span>
                    <a href="{{ route('submissions.download', $submission) }}" target="_blank" class="min-w-0 flex-1 truncate font-bold hover:text-brand-600">{{ $submission->title }}</a>
                    <span class="text-xs text-muted">{{ $submission->sizeLabel() }}</span>
                    <form method="POST" action="{{ route('submissions.destroy', $submission) }}" onsubmit="return confirm('Hapus berkas ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="link-danger">Hapus</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-muted">Belum ada berkas.</p>
            @endforelse

            <form action="{{ route('submissions.store') }}" method="POST" enctype="multipart/form-data" class="grid gap-3 rounded-xl bg-brand-50/60 p-4 sm:grid-cols-4 sm:items-end">
                @csrf
                <div>
                    <label for="type" class="label">Jenis</label>
                    <select id="type" name="type" class="select">
                        <option value="cv">CV</option>
                        <option value="essay">Esai</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label for="file" class="label">Berkas PDF</label>
                    <input id="file" type="file" name="file" accept="application/pdf" required class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:font-bold file:text-brand-600">
                </div>
                <button type="submit" class="btn btn-primary">Unggah</button>
            </form>
        </section>
    </div>
</div>
@endsection

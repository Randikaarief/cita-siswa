@extends('panel.layout')
@section('title', 'Dashboard admin')

@section('content')
<h1 class="h2">Dashboard admin</h1>

<div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
    @foreach ([['Siswa', $students, '/admin/students'], ['Mentor', $mentors, '/admin/mentors'], ['Beasiswa', $scholarships, '/admin/scholarships'], ['Lomba', $competitions, '/admin/competitions'], ['Pengajuan menunggu', $pending, null]] as [$label, $count, $href])
        <div class="card card-pad">
            <p class="text-sm text-muted">{{ $label }}</p>
            <p class="text-3xl font-extrabold">{{ $count }}</p>
            @if ($href)<a href="{{ $href }}" class="link text-xs">Kelola &rarr;</a>@endif
        </div>
    @endforeach
</div>
<p class="help">Angka beasiswa, lomba, dan mentor tidak menghitung data contoh.</p>

@if ($samples > 0)
    <div class="notice notice-warn">
        Masih ada {{ $samples }} data contoh yang tampil ke siswa.
        <a href="{{ route('admin.samples') }}" class="font-semibold underline">Sembunyikan atau hapus sebelum peluncuran</a>
    </div>
@endif
@endsection

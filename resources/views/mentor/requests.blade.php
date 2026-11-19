@extends('panel.layout')
@section('title', 'Pengajuan')

@section('content')
<div class="space-y-3">
    <h1 class="h2">Pengajuan masukan</h1>
    <div class="flex flex-wrap gap-2">
        @foreach ([null => 'Semua', 'pending' => 'Menunggu', 'replied' => 'Sudah dibalas', 'declined' => 'Belum bisa membantu', 'cancelled' => 'Dibatalkan', 'expired' => 'Belum ada balasan'] as $key => $label)
            <a href="{{ $key ? route('mentor.requests', ['status' => $key]) : route('mentor.requests') }}"
               class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-line' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

<p class="text-sm text-muted">
    Kamu membantu secara sukarela. Buka pengajuan untuk membaca berkas dan menulis masukan, atau pilih "Belum bisa membantu" kalau sedang tidak sempat.
    Berkas hanya bisa dibuka selama pengajuan masih terbuka.
</p>

@include('panel._requests_table')
<div>{{ $requests->links() }}</div>
@endsection

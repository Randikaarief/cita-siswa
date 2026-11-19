@extends('panel.layout')
@section('title', 'Testimoni')

@section('content')
<div class="max-w-3xl space-y-4">
    <h1 class="h2">Testimoni saya</h1>

    <form action="{{ route('mentor.testimonials.store') }}" method="POST" class="card card-pad space-y-4">
        @csrf
        @include('panel._field', ['name' => 'youtube', 'label' => 'Link video YouTube (opsional)', 'placeholder' => 'https://youtu.be/...'])
        @include('panel._field', ['name' => 'quote', 'label' => 'Teks testimoni (opsional)', 'type' => 'textarea', 'rows' => 3])
        <button type="submit" class="btn btn-primary">Tambah</button>
    </form>

    <div class="card divide-y divide-line">
        @forelse ($testimonials as $t)
            <div class="flex items-start justify-between gap-4 p-4 text-sm">
                <div class="space-y-1">
                    @if ($t->quote)<p class="text-muted">{{ $t->quote }}</p>@endif
                    @if ($t->youtube)<p class="help">Video: youtu.be/{{ $t->youtube }}</p>@endif
                    @unless ($t->is_visible)<p class="text-amber-700">Disembunyikan admin</p>@endunless
                </div>
                <form method="POST" action="{{ route('mentor.testimonials.destroy', $t->id) }}" onsubmit="return confirm('Hapus testimoni ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="link-danger">Hapus</button>
                </form>
            </div>
        @empty
            <p class="p-6 text-center text-sm text-muted">Belum ada testimoni.</p>
        @endforelse
    </div>
</div>
@endsection

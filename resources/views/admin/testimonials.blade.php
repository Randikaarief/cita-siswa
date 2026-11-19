@extends('panel.layout')
@section('title', 'Testimoni')

@section('content')
<div class="max-w-4xl space-y-4">
    <h1 class="h2">Testimoni</h1>

    <form action="{{ route('admin.testimonials.store') }}" method="POST" class="card card-pad space-y-4">
        @csrf
        <div>
            <label for="mentor_profile_id" class="label">Mentor</label>
            <select id="mentor_profile_id" name="mentor_profile_id" required class="select">
                <option value="" disabled selected>Pilih mentor</option>
                @foreach ($mentors as $m)
                    <option value="{{ $m->id }}" @selected((int) old('mentor_profile_id') === $m->id)>{{ $m->nama }}</option>
                @endforeach
            </select>
        </div>
        @include('panel._field', ['name' => 'youtube', 'label' => 'Link video YouTube (opsional)', 'placeholder' => 'https://youtu.be/...'])
        @include('panel._field', ['name' => 'quote', 'label' => 'Teks testimoni (opsional)', 'type' => 'textarea', 'rows' => 3])
        <button type="submit" class="btn btn-primary">Tambah</button>
    </form>

    <div class="card divide-y divide-line">
        @forelse ($items as $t)
            <div class="flex items-start justify-between gap-4 p-4 text-sm">
                <div class="space-y-1">
                    <p class="font-bold">{{ $t->mentorProfile?->nama ?? '-' }}
                        @if ($t->is_sample)<span class="text-amber-700"> (Contoh)</span>@endif
                        @unless ($t->is_visible)<span class="text-muted"> (Disembunyikan)</span>@endunless
                    </p>
                    @if ($t->quote)<p class="text-muted">{{ $t->quote }}</p>@endif
                    @if ($t->youtube)<p class="help">Video: youtu.be/{{ $t->youtube }}</p>@endif
                </div>
                <div class="flex gap-3 whitespace-nowrap">
                    <form method="POST" action="{{ route('admin.testimonials.toggle', $t->id) }}">
                        @csrf
                        <button type="submit" class="link">{{ $t->is_visible ? 'Sembunyikan' : 'Tampilkan' }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.testimonials.destroy', $t->id) }}" onsubmit="return confirm('Hapus testimoni ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="link-danger">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="p-6 text-center text-sm text-muted">Belum ada testimoni.</p>
        @endforelse
    </div>
    <div>{{ $items->links() }}</div>
</div>
@endsection

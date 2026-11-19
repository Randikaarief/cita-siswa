@extends('panel.layout')
@section('title', 'Beasiswa')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <h1 class="h2">Beasiswa</h1>
    <a href="{{ route('admin.scholarships.create') }}" class="btn btn-primary">Tambah beasiswa</a>
</div>

<form method="GET" class="flex gap-2">
    <input name="q" value="{{ $q }}" placeholder="Cari nama beasiswa" class="input max-w-sm">
    <button class="btn btn-soft btn-sm">Cari</button>
</form>

<div class="tbl-wrap">
    <table class="tbl">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Link resmi</th>
                <th>Terakhir dicek</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr>
                    <td class="font-bold">{{ $item->nama }}</td>
                    <td>{{ $item->kategori ?: '-' }}</td>
                    <td class="max-w-[14rem] truncate">{{ $item->website ?: 'belum diisi' }}</td>
                    <td class="whitespace-nowrap">{{ $item->last_checked_at?->format('d M Y') ?? '-' }}</td>
                    <td class="whitespace-nowrap">
                        @if ($item->is_sample)<span class="badge badge-yellow">Contoh</span>@endif
                        @unless ($item->is_visible)<span class="text-muted">Disembunyikan</span>@endunless
                    </td>
                    <td class="whitespace-nowrap text-right">
                        <a href="{{ route('admin.scholarships.edit', $item->id) }}" class="link mr-3">Edit</a>
                        <form method="POST" action="{{ route('admin.scholarships.destroy', $item->id) }}" class="inline" onsubmit="return confirm('Hapus beasiswa ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="link-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Belum ada beasiswa.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div>{{ $items->links() }}</div>
@endsection

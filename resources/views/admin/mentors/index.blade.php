@extends('panel.layout')
@section('title', 'Mentor')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <h1 class="h2">Mentor</h1>
    <a href="{{ route('admin.mentors.create') }}" class="btn btn-primary">Buat akun mentor</a>
</div>

<form method="GET" class="flex gap-2">
    <input name="q" value="{{ $q }}" placeholder="Cari nama mentor" class="input max-w-sm">
    <button class="btn btn-soft btn-sm">Cari</button>
</form>

<div class="tbl-wrap">
    <table class="tbl">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Akun</th>
                <th>Bersedia membantu</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr>
                    <td class="font-bold">{{ $item->nama }}</td>
                    <td>
                        @if ($item->user)
                            {{ $item->user->email }}
                            @if ($item->user->must_change_password)<span class="text-amber-700"> (password sementara)</span>@endif
                        @else
                            <span class="help">belum ada akun</span>
                        @endif
                    </td>
                    <td>{{ $item->is_willing_to_help ? 'Ya' : 'Tidak' }}</td>
                    <td class="whitespace-nowrap">
                        @if ($item->is_sample)<span class="badge badge-yellow">Contoh</span>@endif
                        @if ($item->user && ! $item->user->is_active)<span class="badge badge-red">Dinonaktifkan</span>
                        @elseif (! $item->is_visible)<span class="text-muted">Disembunyikan</span>@endif
                    </td>
                    <td class="whitespace-nowrap text-right">
                        <a href="{{ route('admin.mentors.edit', $item->id) }}" class="link">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">Belum ada mentor.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div>{{ $items->links() }}</div>
@endsection

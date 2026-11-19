@extends('panel.layout')
@section('title', 'Siswa')

@section('content')
<div class="space-y-4">
    <h1 class="h2">Siswa</h1>
    <p class="text-sm text-muted">Hanya nama dan email. Berkas dan data pribadi siswa tidak bisa dilihat admin. Karena belum ada fitur lupa password, siswa yang lupa password bisa dibantu lewat atur ulang di sini.</p>

    <form method="GET" class="flex gap-2">
        <input name="q" value="{{ $q }}" placeholder="Cari nama atau email" class="input max-w-sm">
        <button class="btn btn-soft btn-sm">Cari</button>
    </form>

    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Daftar</th>
                    <th>Atur ulang password</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $s)
                    <tr>
                        <td class="font-bold">{{ $s->name }}</td>
                        <td>{{ $s->email }}</td>
                        <td class="whitespace-nowrap text-muted">{{ $s->created_at->format('d M Y') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.students.reset', $s->id) }}" class="flex gap-2">
                                @csrf
                                <input name="password" type="text" minlength="8" placeholder="Password baru" required autocomplete="off" class="input !w-44 !py-1.5">
                                <button type="submit" class="btn btn-soft btn-sm">Atur</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">Belum ada siswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $items->links() }}</div>
</div>
@endsection

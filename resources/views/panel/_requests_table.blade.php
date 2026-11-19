{{-- Tabel pengajuan untuk mentor: hanya nama siswa, beasiswa/lomba, jumlah berkas, tanggal, status. --}}
<div class="tbl-wrap">
    <table class="tbl">
        <thead>
            <tr>
                <th>Siswa</th>
                <th>Beasiswa / lomba</th>
                <th>Berkas</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requests as $r)
                @php($target = $r->scholarship ?? $r->competition)
                <tr>
                    <td class="font-bold">{{ $r->user?->name ?? '-' }}</td>
                    <td>{{ $target?->nama ?? '-' }}</td>
                    <td>{{ $r->submissions_count }}</td>
                    <td class="whitespace-nowrap text-muted">{{ $r->created_at->translatedFormat('d M Y, H:i') }}</td>
                    <td><span class="badge {{ $r->statusBadge() }}">{{ $r->statusLabel() }}</span></td>
                    <td class="text-right"><a href="{{ route('mentor.requests.show', $r) }}" class="btn btn-soft btn-sm">Buka</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Belum ada pengajuan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

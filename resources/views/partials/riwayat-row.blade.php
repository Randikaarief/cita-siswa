{{-- Satu baris riwayat: beasiswa/lomba + (kalau ada) mentor dan statusnya. --}}
<article class="card card-pad space-y-3">
    <div class="flex flex-wrap items-start gap-3">
        <span class="logo-tile {{ $row['tile'] }}">{{ $row['logo'] }}</span>
        <div class="min-w-0 flex-1">
            <a href="{{ $row['detail_url'] }}" class="font-extrabold leading-snug hover:text-brand-600">{{ $row['name'] }}</a>
            <p class="text-xs text-muted">{{ $row['date'] }}</p>
            @if ($row['mentor'])
                <p class="mt-1 text-sm"><span class="font-bold">Mentor:</span> {{ $row['mentor'] }}</p>
            @endif
        </div>
        <span class="badge {{ $row['status_badge'] }}">{{ $row['status'] }}</span>
    </div>

    @if ($row['request_id'])
        <details class="rounded-xl bg-brand-50/60 px-4 py-3 text-sm">
            <summary class="cursor-pointer font-bold text-brand-600">Lihat detail pengajuan</summary>
            <div class="mt-3 space-y-3">
                @if ($row['files'])
                    <p><span class="font-bold">Berkas dibagikan:</span> {{ implode(', ', $row['files']) }}</p>
                @endif
                @if ($row['note'])
                    <p><span class="font-bold">Catatanmu:</span> {{ $row['note'] }}</p>
                @endif
                @if ($row['feedback'])
                    <div class="rounded-xl border border-line bg-white p-3">
                        <p class="text-xs font-extrabold uppercase tracking-wide text-brand-500">Masukan dari mentor</p>
                        <p class="mt-1 whitespace-pre-line">{{ $row['feedback'] }}</p>
                    </div>
                @endif
                @if ($row['decline_reason'])
                    <p><span class="font-bold">Alasan mentor:</span> {{ $row['decline_reason'] }}</p>
                @endif
                @if ($row['status_key'] === 'declined' || $row['status_key'] === 'expired' || $row['status_key'] === 'cancelled')
                    <p class="text-muted">Kamu bisa minta masukan ke mentor lain lewat halaman beasiswanya.</p>
                @endif
                <div class="flex flex-wrap items-center gap-3">
                    @if ($row['wa_url'])
                        <a href="{{ $row['wa_url'] }}" target="_blank" rel="noopener" class="btn btn-soft btn-sm">Chat WhatsApp mentor</a>
                    @endif
                    @if ($row['can_cancel'])
                        <form method="POST" action="{{ route('review-requests.cancel', $row['request_id']) }}" onsubmit="return confirm('Batalkan pengajuan ini?')">
                            @csrf
                            <button type="submit" class="link-danger">Batalkan pengajuan</button>
                        </form>
                    @endif
                </div>
            </div>
        </details>
    @else
        <a href="{{ $row['detail_url'] }}" class="link text-sm">Lihat detail &rarr;</a>
    @endif
</article>

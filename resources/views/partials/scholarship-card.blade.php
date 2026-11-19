<a href="/find-scholarship/{{ $s['id'] }}" class="card card-hover card-pad flex flex-col gap-3">
    <div class="flex items-start gap-3">
        <span class="logo-tile {{ \App\Support\Palette::tile($s['id']) }}">{{ $s['logo_text'] ?: \App\Support\Palette::initial($s['nama']) }}</span>
        <div class="min-w-0">
            <h3 class="font-extrabold leading-snug">{{ $s['nama'] }}</h3>
            @if ($s['sub'])<p class="text-sm text-muted line-clamp-1">{{ $s['sub'] }}</p>@endif
        </div>
    </div>
    <div class="flex flex-wrap gap-1.5">
        @if ($s['kategori'])<span class="badge badge-blue">{{ $s['kategori'] }}</span>@endif
        @if ($s['level'])<span class="badge badge-gray">{{ $s['level'] }}</span>@endif
        @if ($s['lokasi'])<span class="badge badge-gray">{{ $s['lokasi'] }}</span>@endif
    </div>
    @if ($s['desc'])<p class="text-sm text-muted line-clamp-2">{{ $s['desc'] }}</p>@endif
    <p class="mt-auto text-xs font-bold text-brand-500">Lihat detail &rarr;</p>
</a>

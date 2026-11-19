<a href="/lomba/{{ $c['id'] }}" class="card card-hover card-pad flex flex-col gap-3">
    <div class="flex items-start gap-3">
        <span class="logo-tile {{ \App\Support\Palette::tile('c'.$c['id']) }}">{{ \App\Support\Palette::initial($c['nama']) }}</span>
        <div class="min-w-0">
            <h3 class="font-extrabold leading-snug">{{ $c['nama'] }}</h3>
            @if ($c['tipe'])<p class="text-sm text-muted line-clamp-1">{{ $c['tipe'] }}</p>@endif
        </div>
    </div>
    <div class="flex flex-wrap gap-1.5">
        @if ($c['kategori'])<span class="badge badge-blue">{{ $c['kategori'] }}</span>@endif
        @if ($c['mode'])<span class="badge badge-yellow">{{ $c['mode'] }}</span>@endif
        @if ($c['lokasi'])<span class="badge badge-gray">{{ $c['lokasi'] }}</span>@endif
    </div>
    @if ($c['description'])<p class="text-sm text-muted line-clamp-2">{{ $c['description'] }}</p>@endif
    <p class="mt-auto text-xs font-bold text-brand-500">Lihat detail &rarr;</p>
</a>

<a href="/alumni/{{ $m['id'] }}" class="card card-hover card-pad flex flex-col gap-3">
    <div class="flex items-center gap-3">
        <span class="avatar !size-12 !text-base">{{ \App\Support\Palette::initial($m['nama']) }}</span>
        <div class="min-w-0">
            <h3 class="font-extrabold leading-snug">{{ $m['nama'] }}</h3>
            @if ($m['lokasi'])<p class="text-sm text-muted line-clamp-1">{{ $m['lokasi'] }}</p>@endif
        </div>
    </div>
    @if ($m['beasiswa'])<p class="text-sm"><span class="font-bold">Beasiswa:</span> {{ $m['beasiswa'] }}</p>@endif
    @if ($m['kategori'])<div><span class="badge badge-blue">{{ $m['kategori'] }}</span></div>@endif
    <p class="mt-auto text-xs font-bold text-brand-500">Kenalan &rarr;</p>
</a>

<form action="{{ $action }}" method="GET" class="card card-pad grid gap-3 sm:grid-cols-4">
    <div class="sm:col-span-2">
        <label for="q" class="label">Cari</label>
        <input id="q" name="q" value="{{ $q }}" placeholder="{{ $placeholder }}" class="input" autocomplete="off">
    </div>
    <div>
        <label for="lokasi" class="label">Lokasi</label>
        <input id="lokasi" name="lokasi" value="{{ $lokasi }}" placeholder="Contoh: Kediri" class="input" autocomplete="off">
    </div>
    <div>
        <label for="kategori" class="label">Kategori</label>
        <select id="kategori" name="kategori" class="select">
            <option value="">Semua</option>
            @foreach (['Pemerintah', 'Private Company', 'Lembaga Internasional'] as $option)
                <option value="{{ $option }}" @selected($kategori === $option)>{{ $option }}</option>
            @endforeach
        </select>
    </div>
    <div class="sm:col-span-4 flex flex-wrap items-center gap-3">
        <button type="submit" class="btn btn-primary">Cari</button>
        @if ($q !== '' || $lokasi !== '' || $kategori !== '')
            <a href="{{ $action }}" class="link text-sm">Hapus pencarian</a>
        @endif
    </div>
</form>

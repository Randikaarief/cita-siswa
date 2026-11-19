@php
    $items = [
        ['/dashboard', 'Dashboard', request()->is('dashboard')],
        ['/riwayat', 'Riwayat', request()->is('riwayat*')],
        ['/alert', 'Notifikasi', request()->is('alert')],
        ['/settings/personal', 'Pengaturan', request()->is('settings*')],
    ];
@endphp
<nav class="card p-2 flex md:flex-col gap-1 overflow-x-auto" aria-label="Menu akun">
    @foreach ($items as [$href, $label, $active])
        <a href="{{ $href }}" class="nav-link whitespace-nowrap {{ $active ? 'nav-link-active' : '' }}" @if($active) aria-current="page" @endif>{{ $label }}
            @if ($href === '/alert' && ($unreadAlerts ?? 0) > 0)<span class="badge badge-yellow ml-1">{{ $unreadAlerts }}</span>@endif
        </a>
    @endforeach
</nav>

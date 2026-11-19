@php
    $tabs = [
        ['/settings/personal', 'Data diri', request()->is('settings/personal')],
        ['/settings/profile', 'Profil', request()->is('settings/profile')],
        ['/settings/social', 'Media sosial', request()->is('settings/social')],
        ['/settings/account', 'Akun', request()->is('settings/account')],
    ];
@endphp
<div class="flex gap-1 overflow-x-auto border-b border-line">
    @foreach ($tabs as [$href, $label, $active])
        <a href="{{ $href }}" class="whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-bold {{ $active ? 'border-brand-500 text-brand-600' : 'border-transparent text-muted hover:text-ink' }}" @if($active) aria-current="page" @endif>{{ $label }}</a>
    @endforeach
</div>

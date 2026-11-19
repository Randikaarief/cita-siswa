<footer class="mt-8 border-t border-line bg-white">
    <div class="page py-8 grid gap-6 sm:grid-cols-3">
        <div class="sm:col-span-2 space-y-3">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('site.name') }}" width="360" height="296" class="h-12 w-auto" loading="lazy">
            <p class="text-sm font-bold">{{ config('site.name') }} <span class="font-semibold text-muted">| {{ config('site.tagline') }}</span></p>
            <p class="max-w-xl text-sm text-muted">{{ config('site.disclaimer') }}</p>
        </div>
        <div class="space-y-2 text-sm">
            <p class="font-extrabold">Kontak</p>
            <p class="text-muted">{{ config('site.contact') }}</p>
        </div>
    </div>
    <div class="border-t border-line py-3 text-center text-xs text-muted">
        &copy; {{ date('Y') }} {{ config('site.name') }}
    </div>
</footer>

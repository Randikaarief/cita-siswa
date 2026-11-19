// Skrip kecil tanpa library. Menu memakai <details>, jadi hampir semua halaman tidak butuh JavaScript.

// Menutup menu dropdown saat klik di luar.
document.addEventListener('click', (event) => {
    document.querySelectorAll('details[data-dropdown][open]').forEach((el) => {
        if (!el.contains(event.target)) el.removeAttribute('open');
    });
});

// Pembuat link UTM (halaman admin Corong).
const builder = document.getElementById('utm-builder');
if (builder) {
    const out = builder.querySelector('[data-utm-output]');
    const update = () => {
        const get = (name) => builder.querySelector(`[name="${name}"]`).value.trim();
        const base = get('base').replace(/\/+$/, '') || window.location.origin;
        const params = new URLSearchParams();
        ['utm_source', 'utm_medium', 'utm_campaign'].forEach((key) => {
            const value = get(key).toLowerCase().replace(/\s+/g, '-');
            if (value) params.set(key, value);
        });
        out.value = params.toString() ? `${base}/?${params.toString()}` : base;
    };
    builder.addEventListener('input', update);
    builder.querySelector('[data-utm-copy]')?.addEventListener('click', () => {
        out.select();
        navigator.clipboard?.writeText(out.value);
    });
    update();
}

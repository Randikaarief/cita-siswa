<?php

/*
| Pengaturan tampilan website. Ganti di sini, berlaku di semua halaman.
| (Teks "test" adalah pengisi sementara.)
*/
return [
    'name' => env('SITE_NAME', 'CITA SISWA'),
    'tagline' => env('SITE_TAGLINE', 'Beasiswa Desa Jarak, Kediri'),

    // Kontak yang tampil di footer. Isi dengan kontak resmi program.
    'contact' => env('SITE_CONTACT', 'test'),

    'disclaimer' => 'CITA SISWA bukan penyelenggara beasiswa. Pendaftaran, syarat, dan jadwal resmi ada di situs penyelenggara masing-masing.',

    // ID Google Analytics 4 (contoh: G-XXXXXXXXXX). Kosong = tidak dipasang.
    'ga4_id' => env('GA4_ID'),

    // Pengajuan masukan ke mentor yang tidak dijawab sekian hari dianggap "belum ada balasan".
    'request_expire_days' => 14,

    // Lama sumber kampanye (UTM) diingat untuk pengunjung, dalam hari.
    'utm_days' => 30,
];

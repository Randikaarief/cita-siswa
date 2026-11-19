<?php

// Pastikan direktori sementara /tmp tersedia untuk Laravel di lingkungan serverless Vercel
$storageDirs = [
    '/tmp/storage/app/public',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Salin database SQLite demo ke /tmp agar dapat ditulisi (writable) oleh serverless lambda
$sourceDb = __DIR__ . '/../database/database.sqlite';
$targetDb = '/tmp/database.sqlite';

if (file_exists($sourceDb) && !file_exists($targetDb)) {
    @copy($sourceDb, $targetDb);
}

// Teruskan penanganan request ke public/index.php bawaan Laravel
require __DIR__ . '/../public/index.php';

<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Setup folder /tmp agar Laravel bisa menulis cache, session, views, dan logs di serverless Vercel
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

// Salin database SQLite demo ke /tmp agar writable oleh serverless lambda
$sourceDb = __DIR__ . '/../database/database.sqlite';
$targetDb = '/tmp/database.sqlite';

if (file_exists($sourceDb) && !file_exists($targetDb)) {
    @copy($sourceDb, $targetDb);
}

// Pastikan script name bersih untuk routing Laravel
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Register Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request
/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->handleRequest(Request::capture());

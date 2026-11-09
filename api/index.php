<?php

// Buat folder penyimpan sementara di /tmp untuk Vercel Serverless
$storagePaths = [
    '/tmp/framework/views',
    '/tmp/framework/sessions',
    '/tmp/framework/cache',
];

foreach ($storagePaths as $path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

// Paksa Laravel menggunakan folder /tmp untuk compiled views
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/framework/views';

require __DIR__ . '/../public/index.php';
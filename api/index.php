<?php

// Buat direktori sementara di /tmp untuk Vercel Serverless
$dirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/bootstrap/cache',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Set lokasi cache ke /tmp agar tidak menulis ke file system Vercel yang read-only
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
putenv('APP_SERVICES_CACHE=/tmp/storage/bootstrap/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/storage/bootstrap/packages.php');
putenv('APP_CONFIG_CACHE=/tmp/storage/bootstrap/config.php');
putenv('APP_ROUTES_CACHE=/tmp/storage/bootstrap/routes.php');

require __DIR__ . '/../public/index.php';
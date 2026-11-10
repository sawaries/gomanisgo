<?php

// Arahkan seluruh storage Laravel ke direktori writable Vercel
$storagePath = '/tmp/storage';

// Buat direktori yang diperlukan Laravel
$dirs = [
    $storagePath.'/app/private',
    $storagePath.'/app/public',
    $storagePath.'/framework/cache/data',
    $storagePath.'/framework/sessions',
    $storagePath.'/framework/views',
    $storagePath.'/logs',
    '/tmp/bootstrap/cache',
];

foreach ($dirs as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Laravel 13 mendukung LARAVEL_STORAGE_PATH
$_ENV['LARAVEL_STORAGE_PATH'] = $storagePath;
$_SERVER['LARAVEL_STORAGE_PATH'] = $storagePath;
putenv('LARAVEL_STORAGE_PATH='.$storagePath);

// Arahkan cache Laravel ke direktori sementara
$cacheFiles = [
    'APP_CONFIG_CACHE' => '/tmp/bootstrap/cache/config.php',
    'APP_ROUTES_CACHE' => '/tmp/bootstrap/cache/routes.php',
    'APP_EVENTS_CACHE' => '/tmp/bootstrap/cache/events.php',
    'APP_SERVICES_CACHE' => '/tmp/bootstrap/cache/services.php',
    'APP_PACKAGES_CACHE' => '/tmp/bootstrap/cache/packages.php',
];

foreach ($cacheFiles as $key => $value) {
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv("$key=$value");
}

// Lokasi compiled Blade
putenv('VIEW_COMPILED_PATH='.$storagePath.'/framework/views');

// Tangani SSL CA jika ada raw text atau file tidak valid
$sslCa = $_ENV['MYSQL_ATTR_SSL_CA'] ?? $_SERVER['MYSQL_ATTR_SSL_CA'] ?? getenv('MYSQL_ATTR_SSL_CA');
if (! empty($sslCa)) {
    if (str_contains($sslCa, 'BEGIN CERTIFICATE')) {
        file_put_contents('/tmp/ca.pem', $sslCa);
        $_ENV['MYSQL_ATTR_SSL_CA'] = '/tmp/ca.pem';
        $_SERVER['MYSQL_ATTR_SSL_CA'] = '/tmp/ca.pem';
        putenv('MYSQL_ATTR_SSL_CA=/tmp/ca.pem');
    } elseif (! file_exists($sslCa)) {
        unset($_ENV['MYSQL_ATTR_SSL_CA'], $_SERVER['MYSQL_ATTR_SSL_CA']);
        putenv('MYSQL_ATTR_SSL_CA');
    }
}

// Jalankan Laravel
require __DIR__.'/../public/index.php';

<?php

// Vercel PHP Runtime - Laravel Entry Point
// This file bridges Vercel's serverless PHP runtime to Laravel

define('LARAVEL_START', microtime(true));

// Set working directory to project root
chdir(dirname(__DIR__));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();

$response = $kernel->handle($request);

$response->send();

$kernel->terminate($request, $response);


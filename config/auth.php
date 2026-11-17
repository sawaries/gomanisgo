<?php


use App\Models\Admin;
use App\Models\User;
use App\Models\Gudang;

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => ['driver' => 'session', 'provider' => 'users'],
        'admin' => ['driver' => 'session', 'provider' => 'admins'],
        'gudang' => ['driver' => 'session', 'provider' => 'gudangs'],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'users' => ['driver' => 'eloquent', 'model' => User::class],
        'admins' => ['driver' => 'eloquent', 'model' => Admin::class],
        'gudangs' => ['driver' => 'eloquent', 'model' => Gudang::class],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];

<?php

return [
    'providers' => [
        App\Providers\AppServiceProvider::class,
        App\Providers\EventServiceProvider::class,

        // Provider necessário para validação funcionar
        Illuminate\Validation\ValidationServiceProvider::class,
    ],

    'name' => env('APP_NAME', 'WMS 4.0'),
    'app_version' => env('APP_VERSION', '1.0.0'),

    'Image' => Intervention\Image\Facades\Image::class,

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'https://systex.com.br/wms'),

    'timezone' => 'America/Sao_Paulo',

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY', 'base64:dHo5Yml6em5yY2txZGNjbGM3MHJ0eWx1c3R4bG12dnk='),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
];

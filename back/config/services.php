<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Firebase: solo para el login con Google de la web publica (config publica del cliente web)
    'firebase' => [
        'api_key' => env('FIREBASE_API_KEY'),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
        'project_id' => env('FIREBASE_PROJECT_ID', ''),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_APP_ID'),
    ],

    // Venta de entradas en la web publica
    'cine' => [
        // Usuario del sistema a cuyo nombre quedan las ventas web (como si el vendiera)
        'vendedor_email' => env('CINE_VENDEDOR_EMAIL', 'vlinea@test.com'),
        // Minutos que se retienen los asientos mientras el cliente paga
        'reserva_minutos' => (int) env('CINE_RESERVA_MINUTOS', 10),
        // Boton "Simular pago" (solo pruebas, hasta conectar el QR real). En produccion: false
        'pago_demo' => (bool) env('CINE_PAGO_DEMO', env('APP_ENV') !== 'production'),
    ],

    'tmdb' => [
        'url' => env('TMDB_API_URL', 'https://api.themoviedb.org/3'),
        'image_url' => env('TMDB_IMAGE_URL', 'https://image.tmdb.org/t/p'),
        'bearer_token' => env('TMDB_BEARER_TOKEN'),
        'api_key' => env('TMDB_API_KEY'),
        'language' => env('TMDB_LANGUAGE', 'es-ES'),
    ],

];

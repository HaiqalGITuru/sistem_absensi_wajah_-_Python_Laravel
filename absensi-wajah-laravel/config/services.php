<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Here you may configure the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file is the proper place
    | to store these credentials.
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

    /*
    |--------------------------------------------------------------------------
    | Python Face Recognition API
    |--------------------------------------------------------------------------
    |
    | URL API Python Flask akan diletakkan di .env.
    | Jangan menulis URL API secara langsung di controller.
    |
    */

    'python_api' => [
        'url' => env('PYTHON_API_URL', 'http://127.0.0.1:5000'),
        'token' => env('PYTHON_API_TOKEN'),
        'timeout' => (int) env('PYTHON_API_TIMEOUT', 5),
    ],

];
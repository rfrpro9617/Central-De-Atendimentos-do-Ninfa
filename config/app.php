<?php

require_once __DIR__ . '/Env.php';

loadEnv(__DIR__ . '/../.env_');

return [
  'app' => [
    'debug' => env('APP_DEBUG', 'false') === 'true',
  ],

  'database' => [
    'host' => env('DB_HOST'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'name' => env('DB_DATABASE'),
    'charset' => env('DB_CHARSET'),
  ],

  'mail' => [
    'host' => env('MAIL_HOST'),
    'username' => env('MAIL_USERNAME'),
    'password' => env('MAIL_PASSWORD'),
    'encryption' => env('MAIL_ENCRYPTION'),
    'port' => (int) env('MAIL_PORT'),
    'from_address' => env('MAIL_FROM_ADDRESS'),
    'from_name' => env('MAIL_FROM_NAME'),
  ],
];

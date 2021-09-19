<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'v1/*', 'oauth/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
      'Origin',
      'Content-Type',
      'Accept',
      'Authorization',
      'X-Requested-With',
      'Application',
      'x-geo-accuracy',
      'x-geo-altitude',
      'x-geo-altitude-accuracy',
      'x-geo-latitude',
      'x-geo-longitude',
      'x-geo-speed'
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];

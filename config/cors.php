<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Options
    |--------------------------------------------------------------------------
    |
    | Configure how your application handles CORS (Cross-Origin Resource Sharing).
    | Adjust these settings according to your requirements.
    |
    */

    'paths' => ['*', 'sanctum/csrf-cookie',],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => ['*'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];

<?php

return [
    'base_url' => env('PGW_BASE_URL', 'https://api-stage-pgw.platform.com.sg'),
    'grant_type' => env('PGW_GRANT_TYPE', 'client_credentials'),
    'client_id' => env('PGW_CLIENT_ID', '4'),
    'client_secret' => env('PGW_CLIENT_SECRET', 'H1E4oHAy4UCyCncB19UNz9HNSjKlUWLin5HzpQIS'),
    'scope' => env('PGW_SCOPE', '*'),
    'hash_key' => env('PGW_HASH_KEY', 'H1E4oHAy4UCyCncB19UNz9HNSjKlUWLin5HzpQIS'),
    'provider' => env('PGW_PROVIDER', 'JDB'),
];

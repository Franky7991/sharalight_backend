<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Configurazione per permettere alla webapp (servita da un'origine
    | diversa, es. http://localhost:3000) di chiamare le API del backend.
    | L'autenticazione avviene via header "Authorization: Bearer <token>",
    | quindi non servono credenziali/cookie.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];

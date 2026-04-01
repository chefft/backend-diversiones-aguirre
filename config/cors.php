<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    // Mantenemos las rutas de la API y el cookie de sesión de Sanctum
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Permitimos todos los métodos (GET, POST, PUT, DELETE)
    'allowed_methods' => ['*'],

    // Especificamos los orígenes de React (Vite suele usar el 5173)
    'allowed_origins' => [
        'http://localhost:5173', 
        'http://127.0.0.1:5173',
        'http://localhost:3000', // Por si acaso usa Create React App
    ],

    'allowed_origins_patterns' => [],

    // Permitimos todos los headers (Content-Type, Authorization, X-Requested-With)
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Cambiamos a true si en el futuro planea usar autenticación por Cookies/Sesiones
    'supports_credentials' => true,

];
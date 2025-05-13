<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseConfig {

    public array $aliases = [
        'csrf' => CSRF::class,
        'toolbar' => DebugToolbar::class,
        'honeypot' => Honeypot::class,
        'invalidchars' => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'autenticacion' => \App\Filters\AutenticacionFilter::class,
        'admin' => \App\Filters\AdminFilter::class,
        'usuario' => \App\Filters\UsuarioFilter::class,
        'cliente' => \App\Filters\ClienteFilter::class,
        'maintenance' => \App\Filters\MaintenanceFilter::class,
         'cors' => \App\Filters\CorsFilter::class,
    ];
    public array $globals = [
        'before' => [
            // 'honeypot',
            // 'csrf',
            // 'invalidchars',
            // 'maintenance', // Esto aplica el filtro globalmente (comentar si no es necesario)
            'csrf' => ['except' => ['api/*', 'reporte/crear', 'getCsrfToken']],
        ],
        'after' => [
            'toolbar',
        // 'honeypot',
        // 'secureheaders',
        ],
    ];
    public array $methods = [];
    public $filters = [
        'auth' => ['before' => ['auth/*']],
        'admin' => ['before' => ['admin/*']],
        'usuario' => ['before' => ['usuario/*']],
        'cliente' => ['before' => ['cliente/*']],
        # Rutas en mantenimiento...
        'maintenance' => [
//            'before' => [
//                "*/programar",
//            ],
        ],
    ];
}

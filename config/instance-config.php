<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Instance
    |--------------------------------------------------------------------------
    |
    | The default instance ID to use when no instance can be detected from
    | request context, configuration, or environment.
    |
    */
    'default' => env('INSTANCE_DEFAULT', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Instance Detection
    |--------------------------------------------------------------------------
    |
    | Configure how instances are detected. Priority:
    | 1. X-Instance header (for API/testing)
    | 2. Config value (instance.default)
    | 3. Default instance
    |
    */
    'detection' => [
        'header' => env('INSTANCE_HEADER', 'X-Instance'),
        'config' => 'instance.default',
    ],

    /*
    |--------------------------------------------------------------------------
    | Key Routing
    |--------------------------------------------------------------------------
    |
    | Maps configuration key prefixes to their respective settings files.
    | Keys not matching any prefix will be routed to main.md.
    |
    */
    'routing' => [
        'site' => 'main.md',
        'contact' => 'main.md',
        'social' => 'main.md',
        'mail' => 'main.md',
        'pms' => 'main.md',
        'pricing' => 'main.md',
        'pages' => 'main.md',
        'services' => 'main.md',
        'theme' => 'theme.md',
        'properties' => 'properties.md',
        'booking' => 'booking.md',
    ],
];

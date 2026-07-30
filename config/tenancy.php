<?php

return [
    'central_hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('CENTRAL_HOSTS', 'localhost,127.0.0.1'))
    ))),

    'base_domain' => env('TENANT_BASE_DOMAIN', 'lvh.me'),

    'database_prefix' => env('TENANT_DATABASE_PREFIX', 'empresa_'),

    'database_user_prefix' => env('TENANT_DATABASE_USER_PREFIX', 'tenant_'),

    'retention_days' => (int) env('TENANT_RETENTION_DAYS', 30),
];

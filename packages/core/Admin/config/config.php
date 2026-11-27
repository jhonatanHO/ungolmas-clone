<?php

/**
 * Configuration for UNGOLMAS Admin module.
 */
return [
    'enabled' => env('UGM_ADMIN_ENABLED', true),

    // NOTE: Seeder exclusivo de local/testing; sin valores usa las claves ficticias del seeder.
    'seed_passwords' => [
        'admin01' => env('ADMIN_SEED_PASSWORD_1'),
        'admin02' => env('ADMIN_SEED_PASSWORD_2'),
    ],
];

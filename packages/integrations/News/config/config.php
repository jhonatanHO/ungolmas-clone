<?php

/**
 * Configuration for UNGOLMAS News module.
 */
return [
    'enabled' => env('UGM_NEWS_ENABLED', true),

    // NOTE: Browser theme metadata requires a literal color rather than a CSS variable.
    'theme_color' => '#000000',

    'wordpress' => [
        'base_url' => env('WP_API', 'https://news.ungolmas.com'),
        'timeout' => (int) env('UGM_NEWS_HTTP_TIMEOUT', 10),
        'connect_timeout' => (int) env('UGM_NEWS_HTTP_CONNECT_TIMEOUT', 3),
        'retries' => (int) env('UGM_NEWS_HTTP_RETRIES', 1),
    ],

    'cache' => [
        'store' => env('UGM_NEWS_CACHE_STORE'),
        'ttl' => (int) env('WORDPRESS_CACHE_TTL', 18000),
    ],
];

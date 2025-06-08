<?php
return [
    'default' => env('CACHE_STORE', 'redis'),
    
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
            // Enable cache tagging for better invalidation
            'tags' => true,
        ],
        
        // High-performance cache for frequently accessed data
        'redis_fast' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'tags' => true,
            'prefix' => 'fast_cache:',
        ],
        
        // Long-term cache for static data
        'redis_long' => [
            'driver' => 'redis', 
            'connection' => 'cache',
            'tags' => true,
            'prefix' => 'long_cache:',
        ],
    ],
    
    'prefix' => env('CACHE_PREFIX', 'invoice_app_cache'),
];
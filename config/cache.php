<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache connection that gets used while
    | using this caching library. This connection is used when another is
    | not explicitly specified when executing a given caching function.
    |
    */

    'default' => env('CACHE_STORE', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    | Supported drivers: "array", "database", "file", "memcached",
    |                    "redis", "dynamodb", "octane", "null"
    |
    */

    'stores' => [

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CACHE_CONNECTION'),
            'table' => env('DB_CACHE_TABLE', 'cache'),
            'prefix' => env('CACHE_PREFIX', ''),
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
            'lock_table' => env('DB_CACHE_LOCK_TABLE'),
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],

        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
            // Enable cache tagging for better invalidation
            'tags' => true,
            'prefix' => env('CACHE_PREFIX', 'invoice_app'),
            // Performance optimizations
            'serializer' => 'php', // Use PHP serializer for better performance
            'compression' => 'lz4', // Enable compression if available
        ],

        'dynamodb' => [
            'driver' => 'dynamodb',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
            'endpoint' => env('DYNAMODB_ENDPOINT'),
        ],

        'octane' => [
            'driver' => 'octane',
        ],
        
        // High-performance cache for frequently accessed data
        'redis_fast' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'tags' => true,
            'prefix' => 'fast_cache',
            'serializer' => 'php',
            'compression' => 'lz4',
        ],
        
        // Long-term cache for static data
        'redis_long' => [
            'driver' => 'redis', 
            'connection' => 'cache',
            'tags' => true,
            'prefix' => 'long_cache',
            'serializer' => 'php',
        ],
        
        // Session cache (separate from main cache)
        'redis_sessions' => [
            'driver' => 'redis',
            'connection' => 'session',
            'prefix' => 'session_cache',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing the "database" or "redis" cache stores, there might be
    | other applications using the same cache. For that reason, you may prefix
    | every cache key to avoid collisions. Define the prefix in the .env file.
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'invoice_app'), '_').'_cache'),

    /*
    |--------------------------------------------------------------------------
    | Cache Tags Configuration
    |--------------------------------------------------------------------------
    |
    | When using Redis as your cache driver, you can use cache tags to group
    | related items together and clear them all at once. This is especially
    | useful for complex applications where you need fine-grained cache control.
    |
    */

    'tags' => [
        'enabled' => env('CACHE_TAGS_ENABLED', true),
        'default_ttl' => env('CACHE_DEFAULT_TTL', 3600), // 1 hour
        'tag_prefix' => env('CACHE_TAG_PREFIX', 'tag'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Performance Settings
    |--------------------------------------------------------------------------
    |
    | These settings help optimize cache performance for your application.
    |
    */

    'performance' => [
        // Enable cache compression to save memory
        'compression' => env('CACHE_COMPRESSION', true),
        
        // Serializer to use (php, igbinary, json)
        'serializer' => env('CACHE_SERIALIZER', 'php'),
        
        // Maximum time to wait for cache operations (seconds)
        'timeout' => env('CACHE_TIMEOUT', 5),
        
        // Retry failed cache operations
        'retry_attempts' => env('CACHE_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('CACHE_RETRY_DELAY', 100), // milliseconds
        
        // Cache warming settings
        'warm_up_on_login' => env('CACHE_WARM_UP_ON_LOGIN', true),
        'warm_up_delay' => env('CACHE_WARM_UP_DELAY', 2), // seconds
        
        // Memory management
        'max_memory_usage' => env('CACHE_MAX_MEMORY', '512M'),
        'memory_policy' => env('CACHE_MEMORY_POLICY', 'allkeys-lru'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Monitoring
    |--------------------------------------------------------------------------
    |
    | Settings for monitoring cache performance and health.
    |
    */

    'monitoring' => [
        'enabled' => env('CACHE_MONITORING_ENABLED', true),
        'log_slow_queries' => env('CACHE_LOG_SLOW_QUERIES', true),
        'slow_query_threshold' => env('CACHE_SLOW_QUERY_THRESHOLD', 100), // milliseconds
        'track_hit_rates' => env('CACHE_TRACK_HIT_RATES', true),
        'alert_on_low_hit_rate' => env('CACHE_ALERT_LOW_HIT_RATE', 70), // percentage
    ],

];
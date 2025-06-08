<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'invoice_app'), '_').'_database_'),
            // Performance optimizations
            'serializer' => env('REDIS_SERIALIZER', 'php'), // php, igbinary, json
            'compression' => env('REDIS_COMPRESSION', 'lz4'), // lz4, zstd, gzip
            'read_timeout' => env('REDIS_READ_TIMEOUT', 60),
            'timeout' => env('REDIS_TIMEOUT', 5),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'read_timeout' => 60,
            'context' => [],
            // Connection pool settings for better performance
            'pool' => [
                'min_connections' => env('REDIS_MIN_CONNECTIONS', 1),
                'max_connections' => env('REDIS_MAX_CONNECTIONS', 10),
                'retry_interval' => env('REDIS_RETRY_INTERVAL', 100), // milliseconds
                'max_idle_time' => env('REDIS_MAX_IDLE_TIME', 300), // seconds
            ],
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'read_timeout' => 60,
            'context' => [],
            // Cache-specific optimizations
            'options' => [
                'maxmemory-policy' => 'allkeys-lru',
                'maxmemory' => env('REDIS_CACHE_MAXMEMORY', '512mb'),
                'tcp-keepalive' => 60,
                'timeout' => 0, // Persistent connections
            ],
        ],

        'session' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_SESSION_DB', '2'),
            'read_timeout' => 60,
            'context' => [],
            // Session-specific settings
            'options' => [
                'maxmemory-policy' => 'allkeys-lru',
                'maxmemory' => env('REDIS_SESSION_MAXMEMORY', '256mb'),
            ],
        ],

        'queue' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_QUEUE_DB', '3'),
            'read_timeout' => 60,
            'context' => [],
            // Queue-specific settings
            'options' => [
                'maxmemory-policy' => 'noeviction', // Don't evict queue jobs
                'appendonly' => 'yes', // Persistence for queue jobs
                'appendfsync' => 'everysec',
            ],
        ],

        // High-performance connection for real-time data
        'realtime' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_REALTIME_DB', '4'),
            'read_timeout' => 30,
            'context' => [],
            'options' => [
                'maxmemory-policy' => 'allkeys-lru',
                'maxmemory' => '128mb',
                'tcp-nodelay' => 'yes', // Low latency
            ],
        ],

        // Analytics and reporting data
        'analytics' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_ANALYTICS_DB', '5'),
            'read_timeout' => 60,
            'context' => [],
            'options' => [
                'maxmemory-policy' => 'allkeys-lru',
                'maxmemory' => '1gb',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Database Query Logging
    |--------------------------------------------------------------------------
    |
    | Enable query logging for performance monitoring and debugging.
    |
    */

    'query_logging' => [
        'enabled' => env('DB_QUERY_LOG', false),
        'slow_query_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 1000), // milliseconds
        'log_all_queries' => env('DB_LOG_ALL_QUERIES', false),
        'channels' => ['single', 'slack'], // Log channels to use
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Performance Settings
    |--------------------------------------------------------------------------
    |
    | Settings to optimize database performance.
    |
    */

    'performance' => [
        'connection_timeout' => env('DB_CONNECTION_TIMEOUT', 60),
        'query_timeout' => env('DB_QUERY_TIMEOUT', 30),
        'max_connections' => env('DB_MAX_CONNECTIONS', 20),
        'idle_timeout' => env('DB_IDLE_TIMEOUT', 300),
        
        // Connection pooling for Redis
        'redis_pool' => [
            'enabled' => env('REDIS_POOL_ENABLED', true),
            'min_connections' => env('REDIS_POOL_MIN', 5),
            'max_connections' => env('REDIS_POOL_MAX', 20),
            'acquire_timeout' => env('REDIS_POOL_ACQUIRE_TIMEOUT', 5000), // milliseconds
        ],
    ],

];
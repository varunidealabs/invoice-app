<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    // Cache duration constants (in seconds)
    const DASHBOARD_STATS_TTL = 300; // 5 minutes
    const RECENT_DATA_TTL = 600; // 10 minutes  
    const CLIENT_LIST_TTL = 1800; // 30 minutes
    const COMPANY_DATA_TTL = 3600; // 1 hour
    const USER_SETTINGS_TTL = 7200; // 2 hours
    const INVOICE_DETAILS_TTL = 1800; // 30 minutes
    const STATUS_COUNTS_TTL = 900; // 15 minutes
    
    // Cache tags for organized invalidation
    const TAGS = [
        'dashboard' => ['dashboard', 'stats'],
        'clients' => ['clients', 'client_data'],
        'invoices' => ['invoices', 'invoice_data'],
        'quotations' => ['quotations', 'quotation_data'],
        'company' => ['company', 'user_data'],
        'reports' => ['reports', 'analytics'],
    ];
    
    /**
     * Get dashboard statistics with caching
     */
    public static function getDashboardStats($companyId)
    {
        $cacheKey = "dashboard_stats_{$companyId}";
        
        return Cache::tags(self::TAGS['dashboard'])
            ->remember($cacheKey, self::DASHBOARD_STATS_TTL, function() use ($companyId) {
                Log::info("Cache MISS: Regenerating dashboard stats for company {$companyId}");
                
                $company = \App\Models\Company::find($companyId);
                
                return [
                    'total_clients' => $company->clients()->count(),
                    'total_invoices' => $company->invoices()->count(),
                    'total_quotations' => $company->quotations()->count(),
                    'pending_invoices' => $company->invoices()->whereIn('status', ['draft', 'sent', 'viewed'])->count(),
                    'overdue_invoices' => $company->invoices()->overdue()->count(),
                    'pending_quotations' => $company->quotations()->whereIn('status', ['draft', 'sent'])->count(),
                    'total_revenue' => $company->invoices()->byStatus('paid')->sum('total'),
                    'pending_amount' => $company->invoices()->whereIn('status', ['sent', 'viewed'])->sum('total'),
                    'generated_at' => now(),
                ];
            });
    }
    
    /**
     * Get status counts with caching
     */
    public static function getStatusCounts($companyId)
    {
        $cacheKey = "status_counts_{$companyId}";
        
        return Cache::tags(['invoices', 'quotations'])
            ->remember($cacheKey, self::STATUS_COUNTS_TTL, function() use ($companyId) {
                Log::info("Cache MISS: Regenerating status counts for company {$companyId}");
                
                $company = \App\Models\Company::find($companyId);
                
                return [
                    'all_invoices' => $company->invoices()->count(),
                    'draft_invoices' => $company->invoices()->byStatus('draft')->count(),
                    'sent_invoices' => $company->invoices()->byStatus('sent')->count(),
                    'paid_invoices' => $company->invoices()->byStatus('paid')->count(),
                    'overdue_invoices' => $company->invoices()->overdue()->count(),
                    
                    'all_quotations' => $company->quotations()->count(),
                    'draft_quotations' => $company->quotations()->byStatus('draft')->count(),
                    'sent_quotations' => $company->quotations()->byStatus('sent')->count(),
                    'accepted_quotations' => $company->quotations()->byStatus('accepted')->count(),
                    'expired_quotations' => $company->quotations()
                        ->where('valid_until', '<', now())
                        ->whereNotIn('status', ['accepted', 'cancelled'])
                        ->count(),
                ];
            });
    }
    
    /**
     * Get all documents (invoices + quotations) with caching
     */
    public static function getAllDocuments($companyId)
    {
        $cacheKey = "all_documents_{$companyId}";
        
        return Cache::tags(['invoices', 'quotations'])
            ->remember($cacheKey, self::RECENT_DATA_TTL, function() use ($companyId) {
                Log::info("Cache MISS: Regenerating all documents for company {$companyId}");
                
                return \App\Models\Company::find($companyId)
                    ->allDocuments()
                    ->with(['client:id,name', 'payments:id,invoice_id,amount'])
                    ->select('id', 'invoice_number', 'client_id', 'is_quotation', 'total', 'status', 'issue_date', 'due_date', 'valid_until', 'created_at')
                    ->latest('created_at')
                    ->get();
            });
    }
    
    /**
     * Get recent clients with caching
     */
    public static function getRecentClients($companyId, $limit = 10)
    {
        $cacheKey = "recent_clients_{$companyId}_{$limit}";
        
        return Cache::tags(self::TAGS['clients'])
            ->remember($cacheKey, self::RECENT_DATA_TTL, function() use ($companyId, $limit) {
                Log::info("Cache MISS: Regenerating recent clients for company {$companyId}");
                
                return \App\Models\Company::find($companyId)
                    ->clients()
                    ->select('id', 'name', 'email', 'contact_person', 'created_at')
                    ->latest()
                    ->take($limit)
                    ->get();
            });
    }
    
    /**
     * Get recent invoices with caching
     */
    public static function getRecentInvoices($companyId, $limit = 10)
    {
        $cacheKey = "recent_invoices_{$companyId}_{$limit}";
        
        return Cache::tags(self::TAGS['invoices'])
            ->remember($cacheKey, self::RECENT_DATA_TTL, function() use ($companyId, $limit) {
                Log::info("Cache MISS: Regenerating recent invoices for company {$companyId}");
                
                return \App\Models\Company::find($companyId)
                    ->invoices()
                    ->with(['client:id,name', 'payments:id,invoice_id,amount'])
                    ->select('id', 'invoice_number', 'client_id', 'total', 'status', 'issue_date', 'due_date')
                    ->latest()
                    ->take($limit)
                    ->get();
            });
    }
    
    /**
     * Get recent quotations with caching
     */
    public static function getRecentQuotations($companyId, $limit = 10)
    {
        $cacheKey = "recent_quotations_{$companyId}_{$limit}";
        
        return Cache::tags(self::TAGS['quotations'])
            ->remember($cacheKey, self::RECENT_DATA_TTL, function() use ($companyId, $limit) {
                Log::info("Cache MISS: Regenerating recent quotations for company {$companyId}");
                
                return \App\Models\Company::find($companyId)
                    ->quotations()
                    ->with(['client:id,name'])
                    ->select('id', 'invoice_number', 'client_id', 'total', 'status', 'issue_date', 'valid_until')
                    ->latest()
                    ->take($limit)
                    ->get();
            });
    }
    
    /**
     * Get company data with caching
     */
    public static function getCompanyData($companyId)
    {
        $cacheKey = "company_data_{$companyId}";
        
        return Cache::tags(self::TAGS['company'])
            ->remember($cacheKey, self::COMPANY_DATA_TTL, function() use ($companyId) {
                Log::info("Cache MISS: Regenerating company data for company {$companyId}");
                
                return \App\Models\Company::with('user')
                    ->find($companyId);
            });
    }
    
    /**
     * Get clients list with caching
     */
    public static function getClientsList($companyId)
    {
        $cacheKey = "clients_list_{$companyId}";
        
        return Cache::tags(self::TAGS['clients'])
            ->remember($cacheKey, self::CLIENT_LIST_TTL, function() use ($companyId) {
                Log::info("Cache MISS: Regenerating clients list for company {$companyId}");
                
                return \App\Models\Company::find($companyId)
                    ->clients()
                    ->select('id', 'name', 'email', 'phone', 'contact_person', 'city', 'state')
                    ->withCount('invoices')
                    ->orderBy('name')
                    ->get();
            });
    }
    
    /**
     * Get user context for ChatGPT with caching
     */
    public static function getUserContext($companyId)
    {
        $cacheKey = "user_context_{$companyId}";
        
        return Cache::tags(['clients', 'invoices', 'quotations'])
            ->remember($cacheKey, 600, function() use ($companyId) { // 10 minutes
                Log::info("Cache MISS: Regenerating user context for company {$companyId}");
                
                $company = \App\Models\Company::find($companyId);
                
                return [
                    'company' => [
                        'id' => $company->id,
                        'name' => $company->company_name,
                        'currency' => $company->currency,
                        'currency_symbol' => $company->currency_symbol,
                        'default_payment_terms' => $company->default_payment_terms,
                        'country' => $company->country,
                    ],
                    'recent_clients' => $company->clients()
                        ->select('id', 'name', 'email', 'contact_person')
                        ->latest()
                        ->take(20)
                        ->get(),
                    'recent_invoices' => $company->allDocuments()
                        ->with('client:id,name')
                        ->select('id', 'invoice_number', 'client_id', 'is_quotation', 'total', 'payment_terms', 'tax_rate')
                        ->latest()
                        ->take(10)
                        ->get(),
                    'common_payment_terms' => $company->allDocuments()
                        ->select('payment_terms')
                        ->groupBy('payment_terms')
                        ->orderByRaw('COUNT(*) DESC')
                        ->take(5)
                        ->pluck('payment_terms'),
                    'common_tax_rate' => $company->allDocuments()
                        ->select('tax_rate')
                        ->groupBy('tax_rate')
                        ->orderByRaw('COUNT(*) DESC')
                        ->value('tax_rate') ?? 0,
                    'current_date' => now()->format('Y-m-d'),
                ];
            });
    }
    
    /**
     * Cache frequently used calculations
     */
    public static function getCalculations($companyId)
    {
        $cacheKey = "calculations_{$companyId}";
        
        return Cache::tags(['invoices', 'quotations'])
            ->remember($cacheKey, 3600, function() use ($companyId) { // 1 hour
                Log::info("Cache MISS: Regenerating calculations for company {$companyId}");
                
                $company = \App\Models\Company::find($companyId);
                
                return [
                    'monthly_revenue' => $company->invoices()
                        ->where('status', 'paid')
                        ->whereMonth('paid_at', now()->month)
                        ->whereYear('paid_at', now()->year)
                        ->sum('total'),
                    'avg_invoice_value' => $company->invoices()->avg('total'),
                    'avg_quotation_value' => $company->quotations()->avg('total'),
                    'conversion_rate' => self::calculateConversionRate($company),
                    'top_clients' => $company->clients()
                        ->withSum('invoices', 'total')
                        ->orderBy('invoices_sum_total', 'desc')
                        ->take(5)
                        ->get(['id', 'name', 'invoices_sum_total']),
                ];
            });
    }
    
    /**
     * Calculate quotation to invoice conversion rate
     */
    private static function calculateConversionRate($company)
    {
        $totalQuotations = $company->quotations()->count();
        $acceptedQuotations = $company->quotations()->where('status', 'accepted')->count();
        
        return $totalQuotations > 0 ? round(($acceptedQuotations / $totalQuotations) * 100, 2) : 0;
    }
    
    /**
     * Warm up cache for a company (useful for new logins)
     */
    public static function warmUpCache($companyId)
    {
        Log::info("Warming up cache for company {$companyId}");
        
        try {
            // Warm up critical caches in parallel if possible
            self::getDashboardStats($companyId);
            self::getClientsList($companyId);
            self::getStatusCounts($companyId);
            self::getRecentInvoices($companyId, 5);
            self::getRecentQuotations($companyId, 5);
            self::getRecentClients($companyId, 5);
            
            Log::info("Cache warm-up completed for company {$companyId}");
        } catch (\Exception $e) {
            Log::error("Cache warm-up failed for company {$companyId}: " . $e->getMessage());
        }
    }
    
    /**
     * Invalidate cache by tags
     */
    public static function invalidateByTags(array $tags)
    {
        foreach ($tags as $tag) {
            if (isset(self::TAGS[$tag])) {
                Cache::tags(self::TAGS[$tag])->flush();
                Log::info("Cache FLUSH: Invalidated {$tag} cache");
            } else {
                // Direct tag flush
                Cache::tags([$tag])->flush();
                Log::info("Cache FLUSH: Invalidated {$tag} cache (direct)");
            }
        }
    }
    
    /**
     * Invalidate specific cache keys
     */
    public static function invalidateKeys(array $keys)
    {
        foreach ($keys as $key) {
            Cache::forget($key);
            Log::info("Cache FORGET: Invalidated key {$key}");
        }
    }
    
    /**
     * Invalidate all cache for a company
     */
    public static function invalidateCompanyCache($companyId)
    {
        // Get all possible cache keys for this company
        $patterns = [
            "dashboard_stats_{$companyId}",
            "status_counts_{$companyId}",
            "all_documents_{$companyId}",
            "recent_clients_{$companyId}*",
            "recent_invoices_{$companyId}*",
            "recent_quotations_{$companyId}*",
            "company_data_{$companyId}",
            "clients_list_{$companyId}",
            "user_context_{$companyId}",
            "calculations_{$companyId}",
        ];
        
        // Use Redis to delete by pattern if available
        try {
            $redis = Redis::connection();
            foreach ($patterns as $pattern) {
                if (str_contains($pattern, '*')) {
                    $keys = $redis->keys("*{$pattern}");
                    if (!empty($keys)) {
                        $redis->del($keys);
                    }
                } else {
                    $redis->del($pattern);
                }
            }
        } catch (\Exception $e) {
            Log::warning("Redis pattern deletion failed, falling back to tag flush: " . $e->getMessage());
        }
        
        // Also flush by tags as backup
        Cache::tags(['dashboard', 'clients', 'invoices', 'quotations', 'company'])->flush();
        
        Log::info("Cache FLUSH: Invalidated all cache for company {$companyId}");
    }
    
    /**
     * Get cache statistics and performance metrics
     */
    public static function getCacheStats()
    {
        try {
            $redis = Cache::getRedis();
            $info = $redis->info();
            
            return [
                'memory' => [
                    'used_memory_human' => $info['used_memory_human'] ?? 'N/A',
                    'used_memory_peak_human' => $info['used_memory_peak_human'] ?? 'N/A',
                    'used_memory_rss_human' => $info['used_memory_rss_human'] ?? 'N/A',
                ],
                'performance' => [
                    'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                    'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                    'hit_rate' => isset($info['keyspace_hits'], $info['keyspace_misses']) 
                        ? round(($info['keyspace_hits'] / ($info['keyspace_hits'] + $info['keyspace_misses'])) * 100, 2) 
                        : 0,
                ],
                'connections' => [
                    'connected_clients' => $info['connected_clients'] ?? 0,
                    'blocked_clients' => $info['blocked_clients'] ?? 0,
                ],
                'operations' => [
                    'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                    'instantaneous_ops_per_sec' => $info['instantaneous_ops_per_sec'] ?? 0,
                ],
                'server' => [
                    'redis_version' => $info['redis_version'] ?? 'Unknown',
                    'uptime_in_seconds' => $info['uptime_in_seconds'] ?? 0,
                    'uptime_in_days' => isset($info['uptime_in_seconds']) ? round($info['uptime_in_seconds'] / 86400, 1) : 0,
                ],
            ];
        } catch (\Exception $e) {
            return ['error' => 'Unable to get Redis stats: ' . $e->getMessage()];
        }
    }
    
    /**
     * Health check for cache system
     */
    public static function healthCheck()
    {
        try {
            $testKey = 'cache_health_check_' . time();
            $testValue = 'OK';
            
            // Test write
            Cache::put($testKey, $testValue, 60);
            
            // Test read
            $retrieved = Cache::get($testKey);
            
            // Test delete
            Cache::forget($testKey);
            
            $isHealthy = $retrieved === $testValue;
            
            return [
                'status' => $isHealthy ? 'healthy' : 'unhealthy',
                'timestamp' => now()->toISOString(),
                'details' => $isHealthy ? 'Cache read/write operations successful' : 'Cache operations failed',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'timestamp' => now()->toISOString(),
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get cache size for monitoring
     */
    public static function getCacheSize()
    {
        try {
            $redis = Redis::connection();
            $dbSize = $redis->dbsize();
            $memoryUsage = $redis->info('memory');
            
            return [
                'total_keys' => $dbSize,
                'memory_usage_bytes' => $memoryUsage['used_memory'] ?? 0,
                'memory_usage_human' => $memoryUsage['used_memory_human'] ?? 'N/A',
            ];
        } catch (\Exception $e) {
            return ['error' => 'Unable to get cache size: ' . $e->getMessage()];
        }
    }
}
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    // Cache duration constants (in seconds)
    const DASHBOARD_STATS_TTL = 300; // 5 minutes
    const RECENT_DATA_TTL = 600; // 10 minutes  
    const CLIENT_LIST_TTL = 1800; // 30 minutes
    const COMPANY_DATA_TTL = 3600; // 1 hour
    const USER_SETTINGS_TTL = 7200; // 2 hours
    
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
                    'total_revenue' => $company->invoices()->byStatus('paid')->sum('total'),
                    'pending_amount' => $company->invoices()->whereIn('status', ['sent', 'viewed'])->sum('total'),
                    'generated_at' => now(),
                ];
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
                    ->select('id', 'name', 'email', 'phone', 'contact_person')
                    ->orderBy('name')
                    ->get();
            });
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
            }
        }
    }
    
    /**
     * Invalidate all cache for a company
     */
    public static function invalidateCompanyCache($companyId)
    {
        $patterns = [
            "dashboard_stats_{$companyId}",
            "recent_clients_{$companyId}*",
            "recent_invoices_{$companyId}*",
            "recent_quotations_{$companyId}*",
            "company_data_{$companyId}",
            "clients_list_{$companyId}",
        ];
        
        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
        
        // Also flush by tags
        Cache::tags(['dashboard', 'clients', 'invoices', 'quotations', 'company'])->flush();
        
        Log::info("Cache FLUSH: Invalidated all cache for company {$companyId}");
    }
    
    /**
     * Get cache statistics
     */
    public static function getCacheStats()
    {
        try {
            $redis = Cache::getRedis();
            $info = $redis->info();
            
            return [
                'used_memory_human' => $info['used_memory_human'] ?? 'N/A',
                'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                'hit_rate' => isset($info['keyspace_hits'], $info['keyspace_misses']) 
                    ? round(($info['keyspace_hits'] / ($info['keyspace_hits'] + $info['keyspace_misses'])) * 100, 2) 
                    : 0,
                'connected_clients' => $info['connected_clients'] ?? 0,
            ];
        } catch (\Exception $e) {
            return ['error' => 'Unable to get Redis stats: ' . $e->getMessage()];
        }
    }
}
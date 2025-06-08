<?php

namespace App\Observers;

use App\Models\Client;
use App\Services\CacheService;

class ClientObserver
{
    public function created(Client $client)
    {
        CacheService::invalidateByTags(['clients', 'dashboard']);
    }
    
    public function updated(Client $client)
    {
        CacheService::invalidateByTags(['clients', 'dashboard']);
    }
    
    public function deleted(Client $client)
    {
        CacheService::invalidateByTags(['clients', 'dashboard']);
    }
}
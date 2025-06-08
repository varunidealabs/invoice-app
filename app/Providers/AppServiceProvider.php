<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Client;
use App\Models\Invoice;
use App\Observers\ClientObserver;
use App\Observers\InvoiceObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register observers for automatic cache invalidation
        Client::observe(ClientObserver::class);
        Invoice::observe(InvoiceObserver::class);
    }
}


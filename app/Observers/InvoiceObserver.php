<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\CacheService;

class InvoiceObserver
{
    public function created(Invoice $invoice)
    {
        $tags = $invoice->is_quotation ? ['quotations', 'dashboard'] : ['invoices', 'dashboard'];
        CacheService::invalidateByTags($tags);
    }
    
    public function updated(Invoice $invoice)
    {
        $tags = $invoice->is_quotation ? ['quotations', 'dashboard'] : ['invoices', 'dashboard'];
        CacheService::invalidateByTags($tags);
    }
    
    public function deleted(Invoice $invoice)
    {
        $tags = $invoice->is_quotation ? ['quotations', 'dashboard'] : ['invoices', 'dashboard'];
        CacheService::invalidateByTags($tags);
    }
}
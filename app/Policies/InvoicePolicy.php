<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasCompany();
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->company->id === $invoice->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasCompany();
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->company->id === $invoice->company_id;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->company->id === $invoice->company_id && 
               $invoice->status !== 'paid';
    }
}
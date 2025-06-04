<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasCompany();
    }

    public function view(User $user, Client $client): bool
    {
        return $user->company->id === $client->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasCompany();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->company->id === $client->company_id;
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->company->id === $client->company_id;
    }
}
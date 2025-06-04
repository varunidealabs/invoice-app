<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function company()
    {
        return $this->hasOne(Company::class);
    }

    public function clients()
    {
        return $this->hasManyThrough(Client::class, Company::class);
    }

    public function invoices()
    {
        return $this->hasManyThrough(Invoice::class, Company::class);
    }

    // Methods
    public function hasCompany(): bool
    {
        return !is_null($this->company);
    }

    public function getCompanyNameAttribute(): string
    {
        return $this->company?->company_name ?? 'No Company Set';
    }
}
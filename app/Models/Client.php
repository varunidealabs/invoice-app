<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'email', 'phone',
        'address_line_1', 'address_line_2', 'city', 
        'state', 'postal_code', 'country',
        'tax_id', 'contact_person'
    ];

    protected $hidden = ['deleted_at'];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Accessors
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state . ' ' . $this->postal_code,
            $this->country
        ]);
        
        return implode(', ', $parts);
    }

    // Scopes
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'company_name', 'company_email', 'company_phone', 'website',
        'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country',
        'tax_id', 'business_type', 'logo',
        'invoice_prefix', 'next_invoice_number', 'quotation_prefix', 'next_quotation_number',
        'default_payment_terms', 'currency',
    ];

    protected $casts = [
        'next_invoice_number' => 'integer',
        'next_quotation_number' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    // Updated relationships to handle both invoices and quotations
    public function invoices()
    {
        return $this->hasMany(Invoice::class)->where('is_quotation', false);
    }

    public function quotations()
    {
        return $this->hasMany(Invoice::class)->where('is_quotation', true);
    }

    // All documents (both invoices and quotations)
    public function allDocuments()
    {
        return $this->hasMany(Invoice::class);
    }

    // Invoice methods
    public function getFormattedNextInvoiceNumberAttribute(): string
    {
        return $this->invoice_prefix . '-' . str_pad($this->next_invoice_number, 3, '0', STR_PAD_LEFT);
    }

    public function getNextInvoiceNumber(): string
    {
        $currentNumber = $this->next_invoice_number;
        $formattedNumber = $this->invoice_prefix . '-' . str_pad($currentNumber, 3, '0', STR_PAD_LEFT);
        
        $this->increment('next_invoice_number');
        
        return $formattedNumber;
    }

    // Quotation methods
    public function getFormattedNextQuotationNumberAttribute(): string
    {
        return $this->quotation_prefix . '-' . str_pad($this->next_quotation_number, 3, '0', STR_PAD_LEFT);
    }

    public function getNextQuotationNumber(): string
    {
        $currentNumber = $this->next_quotation_number;
        $formattedNumber = $this->quotation_prefix . '-' . str_pad($currentNumber, 3, '0', STR_PAD_LEFT);
        
        $this->increment('next_quotation_number');
        
        return $formattedNumber;
    }

    // Existing utility methods
    public function getFullAddressAttribute(): string
    {
        $address = $this->address_line_1;
        
        if ($this->address_line_2) {
            $address .= ', ' . $this->address_line_2;
        }
        
        $address .= ', ' . $this->city;
        $address .= ', ' . $this->state;
        $address .= ' ' . $this->postal_code;
        $address .= ', ' . $this->country;
        
        return $address;
    }

    public function getLogoUrlAttribute(): string
    {
        if ($this->logo && Storage::exists($this->logo)) {
            return Storage::url($this->logo);
        }
        
        return 'data:image/svg+xml;base64,' . base64_encode(
            '<svg width="64" height="64" xmlns="http://www.w3.org/2000/svg">
                <rect width="64" height="64" fill="#3B82F6"/>
                <text x="32" y="40" font-family="Arial" font-size="24" font-weight="bold" 
                      text-anchor="middle" fill="white">' . 
                      substr($this->company_name, 0, 1) . 
                '</text>
            </svg>'
        );
    }

    public function getCurrencySymbolAttribute(): string
    {
        return match($this->currency) {
            'INR' => '₹',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'AUD' => 'A$',
            'CAD' => 'C$',
            default => '₹',
        };
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
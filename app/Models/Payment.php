<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'amount', 'payment_date',
        'method', 'reference', 'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date'
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Boot method to update invoice status
    protected static function booted()
    {
        static::saved(function ($payment) {
            $invoice = $payment->invoice;
            $totalPaid = $invoice->payments->sum('amount');
            
            if ($totalPaid >= $invoice->total) {
                $invoice->markAsPaid();
            }
        });
    }

    // Accessors
    public function getMethodLabelAttribute(): string
    {
        return match($this->method) {
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'check' => 'Check',
            'upi' => 'UPI',
            'card' => 'Card',
            'other' => 'Other',
            default => 'Unknown'
        };
    }
}
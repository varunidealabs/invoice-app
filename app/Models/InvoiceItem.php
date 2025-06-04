<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'description', 'quantity', 
        'unit_price', 'total', 'sort_order'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Boot method to auto-calculate total
    protected static function booted()
    {
        static::saving(function ($item) {
            $item->total = $item->quantity * $item->unit_price;
        });

        static::saved(function ($item) {
            $item->invoice->calculateTotals();
        });

        static::deleted(function ($item) {
            $item->invoice->calculateTotals();
        });
    }
}
<?php
// app/Models/Invoice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'client_id', 'invoice_number', 'is_quotation', 'valid_until', // ADDED: quotation fields
        'issue_date', 'due_date', 'status',
        'subtotal', 'tax_rate', 'tax_amount', 'total',
        'payment_terms', 'notes', 'terms'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'valid_until' => 'date', // ADDED
        'is_quotation' => 'boolean', // ADDED
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    protected $hidden = ['deleted_at'];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // NEW: Document type accessors
    public function getDocumentTypeAttribute(): string
    {
        return $this->is_quotation ? 'Quotation' : 'Invoice';
    }

    public function getDocumentNumberAttribute(): string
    {
        return $this->invoice_number;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->is_quotation && 
               $this->valid_until && 
               $this->valid_until->isPast() && 
               !in_array($this->status, ['accepted', 'cancelled']);
    }

    // Existing accessors - UPDATED
    public function getIsOverdueAttribute(): bool
    {
        // Only invoices can be overdue, not quotations
        return !$this->is_quotation && 
               $this->status !== 'paid' && 
               $this->due_date && 
               $this->due_date->isPast();
    }

    public function getBalanceAttribute(): float
    {
        return $this->total - $this->payments->sum('amount');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'sent' => 'Sent',
            'viewed' => 'Viewed',
            'paid' => 'Paid',
            'accepted' => 'Accepted', // NEW: For quotations
            'expired' => 'Expired', // NEW: For quotations
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }

    // NEW: Scopes for filtering
    public function scopeInvoices($query)
    {
        return $query->where('is_quotation', false);
    }

    public function scopeQuotations($query)
    {
        return $query->where('is_quotation', true);
    }

    // Existing scopes
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_quotation', false) // Only invoices can be overdue
                    ->where('status', '!=', 'paid')
                    ->where('due_date', '<', now());
    }

    // NEW: Quotation-specific methods
    public function convertToInvoice(): self
    {
        if (!$this->is_quotation) {
            throw new \Exception('This is already an invoice');
        }

        $invoice = $this->replicate(['invoice_number']);
        $invoice->is_quotation = false;
        $invoice->valid_until = null;
        $invoice->invoice_number = $this->company->getNextInvoiceNumber();
        $invoice->status = 'draft';
        $invoice->issue_date = now();
        $invoice->due_date = now()->addDays(30);
        $invoice->save();

        // Copy items
        foreach ($this->items as $item) {
            $newItem = $item->replicate();
            $newItem->invoice_id = $invoice->id;
            $newItem->save();
        }

        $invoice->calculateTotals();
        return $invoice;
    }

    public function markAsAccepted()
    {
        if ($this->is_quotation) {
            $this->update(['status' => 'accepted']);
        }
    }

    // PDF-related methods - UPDATED
    public function getPdfFilename(): string
    {
        $clientName = Str::slug($this->client->name);
        $documentType = $this->is_quotation ? 'quotation' : 'invoice';
        return "{$documentType}-{$this->invoice_number}-{$clientName}.pdf";
    }

    public function getPdfPath(): string
    {
        $folder = $this->is_quotation ? 'quotations' : 'invoices';
        return "{$folder}/" . $this->getPdfFilename();
    }

    public function generatePdf(): string
    {
        // Load relationships if not already loaded
        $this->loadMissing(['client', 'company', 'items', 'payments']);
        
        // Generate PDF
        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $this])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        // Store PDF
        $path = $this->getPdfPath();
        Storage::put($path, $pdf->output());
        
        return $path;
    }

    public function downloadPdf()
    {
        $this->loadMissing(['client', 'company', 'items', 'payments']);
        
        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $this])
            ->setPaper('a4', 'portrait');
            
        return $pdf->download($this->getPdfFilename());
    }

    public function streamPdf()
    {
        $this->loadMissing(['client', 'company', 'items', 'payments']);
        
        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $this])
            ->setPaper('a4', 'portrait');
            
        return $pdf->stream($this->getPdfFilename());
    }

    public function getShareableLink(): array
    {
        // Generate and store PDF
        $pdfPath = $this->generatePdf();
        
        // Generate temporary public URL (valid for 24 hours)
        $url = Storage::temporaryUrl($pdfPath, now()->addDay());
        
        $documentType = $this->is_quotation ? 'Quotation' : 'Invoice';
        
        return [
            'pdf_url' => $url,
            'filename' => $this->getPdfFilename(),
            'share_text' => "{$documentType} {$this->invoice_number} from {$this->company->company_name}",
            'whatsapp_url' => "https://wa.me/?text=" . urlencode("Please find the {$documentType}: {$url}"),
            'email_subject' => "{$documentType} {$this->invoice_number}",
            'email_body' => "Please find attached {$documentType} {$this->invoice_number}.\n\nDownload: {$url}"
        ];
    }

    public function deletePdf(): bool
    {
        $path = $this->getPdfPath();
        
        if (Storage::exists($path)) {
            return Storage::delete($path);
        }
        
        return false;
    }

    public function hasPdf(): bool
    {
        return Storage::exists($this->getPdfPath());
    }

    // Existing methods
    public function calculateTotals()
    {
        $this->subtotal = $this->items->sum('total');
        $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);
        $this->total = $this->subtotal + $this->tax_amount;
        $this->save();
    }

    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }

    public function markAsViewed()
    {
        if ($this->status === 'sent') {
            $this->update([
                'status' => 'viewed',
                'viewed_at' => now()
            ]);
        }
    }

    public function markAsPaid()
    {
        // Only invoices can be paid, not quotations
        if (!$this->is_quotation) {
            $this->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);
        }
    }

    // Clean up PDF when invoice is deleted
    protected static function booted()
    {
        static::deleting(function ($invoice) {
            $invoice->deletePdf();
        });
    }
}
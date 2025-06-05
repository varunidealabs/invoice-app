<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'type' => $this->is_quotation ? 'quotation' : 'invoice',
            'status' => $this->status,
            'status_label' => $this->status_label,
            'issue_date' => $this->issue_date->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'valid_until' => $this->valid_until?->format('Y-m-d'),
            'payment_terms' => $this->payment_terms,
            'is_overdue' => $this->is_overdue,
            'is_expired' => $this->is_expired,
            'financials' => [
                'subtotal' => $this->subtotal,
                'tax_rate' => $this->tax_rate,
                'tax_amount' => $this->tax_amount,
                'total' => $this->total,
                'currency' => $this->company->currency,
                'currency_symbol' => $this->company->currency_symbol,
            ],
            'client' => new ClientResource($this->whenLoaded('client')),
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'notes' => $this->notes,
            'terms' => $this->terms,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
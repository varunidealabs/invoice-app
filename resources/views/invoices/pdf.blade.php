<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice->is_quotation ? 'Quotation' : 'Invoice' }} {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .company-info {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .invoice-info {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }
        
        .logo {
            max-height: 80px;
            margin-bottom: 15px;
        }
        
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .invoice-number {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status.paid { background: #d4edda; color: #155724; }
        .status.accepted { background: #d4edda; color: #155724; }
        .status.sent { background: #cce5ff; color: #004085; }
        .status.draft { background: #f8f9fa; color: #495057; }
        .status.overdue { background: #f8d7da; color: #721c24; }
        .status.expired { background: #fff3cd; color: #856404; }
        
        .billing-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .bill-to {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .company-name, .client-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .address-line {
            margin-bottom: 3px;
            color: #666;
        }
        
        .invoice-details {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        
        .invoice-details td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-label {
            font-weight: bold;
            width: 30%;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th {
            background: #f8f9fa;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #dee2e6;
        }
        
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .totals-section {
            float: right;
            width: 300px;
            margin-bottom: 30px;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }
        
        .totals-table .total-row {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #333;
        }
        
        .notes-section {
            clear: both;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .notes-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        /* Quotation-specific styles */
        .quotation-notice {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 11px;
            color: #1976d2;
        }
        
        .validity-warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 8px;
            margin-top: 10px;
            font-size: 10px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                @if($invoice->company->logo && Storage::exists($invoice->company->logo))
                    <img src="{{ public_path('storage/' . $invoice->company->logo) }}" alt="Company Logo" class="logo">
                @endif
                <div class="company-name">{{ $invoice->company->company_name }}</div>
                @if($invoice->company->company_email)
                    <div class="address-line">{{ $invoice->company->company_email }}</div>
                @endif
                @if($invoice->company->company_phone)
                    <div class="address-line">{{ $invoice->company->company_phone }}</div>
                @endif
                <div class="address-line">{{ $invoice->company->address_line_1 }}</div>
                @if($invoice->company->address_line_2)
                    <div class="address-line">{{ $invoice->company->address_line_2 }}</div>
                @endif
                <div class="address-line">{{ $invoice->company->city }}, {{ $invoice->company->state }} {{ $invoice->company->postal_code }}</div>
                <div class="address-line">{{ $invoice->company->country }}</div>
                @if($invoice->company->tax_id)
                    <div class="address-line">Tax ID: {{ $invoice->company->tax_id }}</div>
                @endif
            </div>
            
            <div class="invoice-info">
                <div class="invoice-title">{{ $invoice->is_quotation ? 'QUOTATION' : 'INVOICE' }}</div>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                @php
                    $statusClass = $invoice->status;
                    if ($invoice->is_quotation && $invoice->is_expired) {
                        $statusClass = 'expired';
                    } elseif (!$invoice->is_quotation && $invoice->is_overdue) {
                        $statusClass = 'overdue';
                    }
                @endphp
                <span class="status {{ $statusClass }}">
                    @if($invoice->is_quotation && $invoice->is_expired)
                        Expired
                    @elseif(!$invoice->is_quotation && $invoice->is_overdue)
                        Overdue
                    @else
                        {{ $invoice->status_label }}
                    @endif
                </span>
            </div>
        </div>

        <!-- Quotation Notice -->
        @if($invoice->is_quotation)
            <div class="quotation-notice">
                <strong>QUOTATION NOTICE:</strong> This is a quotation for services/products. This is not an invoice and no payment is due at this time.
                @if($invoice->valid_until)
                    This quotation is valid until {{ $invoice->valid_until->format('F d, Y') }}.
                @endif
            </div>
        @endif

        <!-- Billing Information -->
        <div class="billing-section">
            <div class="bill-to">
                <div class="section-title">{{ $invoice->is_quotation ? 'Quote For:' : 'Bill To:' }}</div>
                <div class="client-name">{{ $invoice->client->name }}</div>
                @if($invoice->client->contact_person)
                    <div class="address-line">{{ $invoice->client->contact_person }}</div>
                @endif
                @if($invoice->client->email)
                    <div class="address-line">{{ $invoice->client->email }}</div>
                @endif
                @if($invoice->client->phone)
                    <div class="address-line">{{ $invoice->client->phone }}</div>
                @endif
                @if($invoice->client->address_line_1)
                    <div class="address-line">{{ $invoice->client->address_line_1 }}</div>
                    @if($invoice->client->address_line_2)
                        <div class="address-line">{{ $invoice->client->address_line_2 }}</div>
                    @endif
                    @if($invoice->client->city)
                        <div class="address-line">{{ $invoice->client->city }}@if($invoice->client->state), {{ $invoice->client->state }}@endif @if($invoice->client->postal_code) {{ $invoice->client->postal_code }}@endif</div>
                    @endif
                    @if($invoice->client->country)
                        <div class="address-line">{{ $invoice->client->country }}</div>
                    @endif
                @endif
                @if($invoice->client->tax_id)
                    <div class="address-line">Tax ID: {{ $invoice->client->tax_id }}</div>
                @endif
            </div>
        </div>

        <!-- Document Details -->
        <table class="invoice-details">
            @if($invoice->is_quotation)
                <tr>
                    <td class="detail-label">Quote Date:</td>
                    <td>{{ $invoice->issue_date->format('F d, Y') }}</td>
                    @if($invoice->valid_until)
                        <td class="detail-label">Valid Until:</td>
                        <td>{{ $invoice->valid_until->format('F d, Y') }}</td>
                    @else
                        <td colspan="2"></td>
                    @endif
                </tr>
            @else
                <tr>
                    <td class="detail-label">Issue Date:</td>
                    <td>{{ $invoice->issue_date->format('F d, Y') }}</td>
                    @if($invoice->due_date)
                        <td class="detail-label">Due Date:</td>
                        <td>{{ $invoice->due_date->format('F d, Y') }}</td>
                    @else
                        <td colspan="2"></td>
                    @endif
                </tr>
            @endif
            @if($invoice->payment_terms)
                <tr>
                    <td class="detail-label">{{ $invoice->is_quotation ? 'Terms:' : 'Payment Terms:' }}</td>
                    <td colspan="3">{{ $invoice->payment_terms }}</td>
                </tr>
            @endif
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right" style="width: 80px;">Qty</th>
                    <th class="text-right" style="width: 100px;">Unit Price</th>
                    <th class="text-right" style="width: 120px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-right">{{ $invoice->company->currency_symbol }}{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">{{ $invoice->company->currency_symbol }}{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">{{ $invoice->company->currency_symbol }}{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->tax_rate > 0)
                    <tr>
                        <td>Tax ({{ number_format($invoice->tax_rate, 2) }}%):</td>
                        <td class="text-right">{{ $invoice->company->currency_symbol }}{{ number_format($invoice->tax_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td>{{ $invoice->is_quotation ? 'Quote Total:' : 'Total:' }}</td>
                    <td class="text-right">{{ $invoice->company->currency_symbol }}{{ number_format($invoice->total, 2) }}</td>
                </tr>
                
                @if(!$invoice->is_quotation && $invoice->payments->sum('amount') > 0)
                    <tr>
                        <td>Paid:</td>
                        <td class="text-right" style="color: #28a745;">{{ $invoice->company->currency_symbol }}{{ number_format($invoice->payments->sum('amount'), 2) }}</td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td>Balance Due:</td>
                        <td class="text-right">{{ $invoice->company->currency_symbol }}{{ number_format($invoice->balance, 2) }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <!-- Validity Warning for Quotations -->
        @if($invoice->is_quotation && $invoice->valid_until && $invoice->valid_until->isPast())
            <div class="validity-warning">
                <strong>WARNING:</strong> This quotation has expired on {{ $invoice->valid_until->format('F d, Y') }}. Please request a new quotation for current pricing.
            </div>
        @endif

        <!-- Notes and Terms -->
        @if($invoice->notes || $invoice->terms)
            <div class="notes-section">
                @if($invoice->notes)
                    <div style="margin-bottom: 20px;">
                        <div class="notes-title">Notes:</div>
                        <div>{!! nl2br(e($invoice->notes)) !!}</div>
                    </div>
                @endif
                @if($invoice->terms)
                    <div>
                        <div class="notes-title">{{ $invoice->is_quotation ? 'Quote Terms & Conditions:' : 'Terms & Conditions:' }}</div>
                        <div>{!! nl2br(e($invoice->terms)) !!}</div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Quotation Acceptance Section -->
        @if($invoice->is_quotation && $invoice->status !== 'accepted')
            <div class="notes-section">
                <div class="notes-title">Quote Acceptance:</div>
                <p style="margin-bottom: 15px;">To accept this quotation, please sign and return this document or send written confirmation.</p>
                
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <tr>
                        <td style="width: 50%; padding: 20px 0; border-bottom: 1px solid #333;">
                            <div style="text-align: center;">
                                <div style="margin-bottom: 5px;">_________________________________</div>
                                <div style="font-size: 10px;">Client Signature</div>
                            </div>
                        </td>
                        <td style="width: 50%; padding: 20px 0; border-bottom: 1px solid #333;">
                            <div style="text-align: center;">
                                <div style="margin-bottom: 5px;">_________________________________</div>
                                <div style="font-size: 10px;">Date</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Generated on {{ now()->format('F d, Y \a\t H:i:s') }}</p>
            @if($invoice->company->website)
                <p>{{ $invoice->company->website }}</p>
            @endif
            @if($invoice->is_quotation)
                <p style="margin-top: 10px; font-style: italic;">This quotation is valid until {{ $invoice->valid_until ? $invoice->valid_until->format('F d, Y') : 'further notice' }}.</p>
            @endif
        </div>
    </div>
</body>
</html>
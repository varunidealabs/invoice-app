<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
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
        .status.sent { background: #cce5ff; color: #004085; }
        .status.draft { background: #f8f9fa; color: #495057; }
        .status.overdue { background: #f8d7da; color: #721c24; }
        
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
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                <span class="status {{ $invoice->is_overdue ? 'overdue' : $invoice->status }}">
                    {{ $invoice->is_overdue ? 'Overdue' : $invoice->status_label }}
                </span>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="billing-section">
            <div class="bill-to">
                <div class="section-title">Bill To:</div>
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

        <!-- Invoice Details -->
        <table class="invoice-details">
            <tr>
                <td class="detail-label">Issue Date:</td>
                <td>{{ $invoice->issue_date->format('F d, Y') }}</td>
                <td class="detail-label">Due Date:</td>
                <td>{{ $invoice->due_date->format('F d, Y') }}</td>
            </tr>
            <tr>
                <td class="detail-label">Payment Terms:</td>
                <td colspan="3">{{ $invoice->payment_terms }}</td>
            </tr>
        </table>

        <!-- Invoice Items -->
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
                    <td>Total:</td>
                    <td class="text-right">{{ $invoice->company->currency_symbol }}{{ number_format($invoice->total, 2) }}</td>
                </tr>
                @if($invoice->payments->sum('amount') > 0)
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
                        <div class="notes-title">Terms & Conditions:</div>
                        <div>{!! nl2br(e($invoice->terms)) !!}</div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Generated on {{ now()->format('F d, Y \a\t H:i:s') }}</p>
            @if($invoice->company->website)
                <p>{{ $invoice->company->website }}</p>
            @endif
        </div>
    </div>
</body>
</html>
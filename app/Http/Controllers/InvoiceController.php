<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');
        $type = $request->get('type', 'invoice'); // NEW: Default to invoices
        
        $query = auth()->user()->company->invoices()
            ->with(['client', 'payments']);
        
        // NEW: Filter by type (invoice or quotation)
        if ($type === 'quotation') {
            $query->quotations();
        } else {
            $query->invoices();
        }
        
        $invoices = $query
            ->when($status, function ($query, $status) use ($type) {
                if ($status === 'overdue') {
                    $query->overdue();
                } elseif ($status === 'expired' && $type === 'quotation') {
                    $query->where('valid_until', '<', now())
                          ->whereNotIn('status', ['accepted', 'cancelled']);
                } else {
                    $query->byStatus($status);
                }
            })
            ->when($search, function ($query, $search) {
                $query->whereHas('client', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('invoice_number', 'like', "%{$search}%");
            })
            ->latest('created_at')
            ->paginate(15);

        $statusCounts = $this->getStatusCounts($type);

        return view('invoices.index', compact('invoices', 'status', 'search', 'statusCounts', 'type'));
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        $invoice->load(['client', 'items', 'payments']);
        
        return view('invoices.show', compact('invoice'));
    }

    public function create(Request $request)
    {
        $clients = auth()->user()->company->clients()
            ->orderBy('name')
            ->get();

        if ($clients->isEmpty()) {
            return redirect()->route('clients.create')
                ->with('info', 'Please create a client first before creating a document.');
        }

        $selectedClientId = $request->get('client');
        $type = $request->get('type', 'invoice'); // NEW
        $isQuotation = $type === 'quotation'; // NEW

        return view('invoices.create', compact('clients', 'selectedClientId', 'isQuotation'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateInvoice($request);
        $isQuotation = $request->boolean('is_quotation'); // NEW
        
        try {
            $invoice = DB::transaction(function () use ($validated, $isQuotation) {
                $company = auth()->user()->company;
                
                $invoice = Invoice::create([
                    'company_id' => $company->id,
                    'client_id' => $validated['client_id'],
                    'invoice_number' => $isQuotation ? 
                        $company->getNextQuotationNumber() : 
                        $company->getNextInvoiceNumber(),
                    'is_quotation' => $isQuotation, // NEW
                    'issue_date' => $validated['issue_date'],
                    'due_date' => $isQuotation ? null : $validated['due_date'], // NEW: Conditional
                    'valid_until' => $isQuotation ? $validated['valid_until'] : null, // NEW
                    'payment_terms' => $validated['payment_terms'],
                    'notes' => $validated['notes'] ?? null,
                    'terms' => $validated['terms'] ?? null,
                    'tax_rate' => $validated['tax_rate'] ?? 0,
                ]);

                foreach ($validated['items'] as $index => $item) {
                    $invoice->items()->create([
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'sort_order' => $index,
                    ]);
                }

                $invoice->calculateTotals();
                return $invoice;
            });

            $type = $isQuotation ? 'quotation' : 'invoice';
            $message = $isQuotation ? 'Quotation created successfully!' : 'Invoice created successfully!';

            return redirect()->route('invoices.show', [$invoice, 'type' => $type])
                ->with('success', $message);
                
        } catch (\Exception $e) {
            \Log::error('Document creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error creating document. Please try again.');
        }
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Cannot edit paid invoices.');
        }

        $clients = auth()->user()->company->clients()
            ->orderBy('name')
            ->get();

        $invoice->load('items');

        return view('invoices.edit', compact('invoice', 'clients'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Cannot edit paid invoices.');
        }

        $validated = $this->validateInvoice($request, $invoice->id);
        
        try {
            DB::transaction(function () use ($invoice, $validated) {
                $invoice->update([
                    'client_id' => $validated['client_id'],
                    'issue_date' => $validated['issue_date'],
                    'due_date' => $validated['due_date'],
                    'payment_terms' => $validated['payment_terms'],
                    'notes' => $validated['notes'] ?? null,
                    'terms' => $validated['terms'] ?? null,
                    'tax_rate' => $validated['tax_rate'] ?? 0,
                ]);

                // Delete existing items and recreate
                $invoice->items()->delete();
                
                foreach ($validated['items'] as $index => $item) {
                    $invoice->items()->create([
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'sort_order' => $index,
                    ]);
                }

                $invoice->calculateTotals();
            });

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Invoice update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error updating invoice. Please try again.');
        }
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);
        
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Cannot delete paid invoices.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully!');
    }

    public function downloadPdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        return $invoice->downloadPdf();
    }

    public function viewPdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        return $invoice->streamPdf();
    }

    public function sharePdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        $shareData = $invoice->getShareableLink();
        
        return response()->json([
            'success' => true,
            ...$shareData
        ]);
    }

    public function markAsSent(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        $invoice->markAsSent();
        
        return back()->with('success', 'Invoice marked as sent!');
    }

    public function markAsPaid(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        $invoice->markAsPaid();
        
        return back()->with('success', 'Invoice marked as paid!');
    }

    // NEW: Add these methods for quotation functionality
    public function convertToInvoice(Invoice $quotation)
    {
        $this->authorize('update', $quotation);
        
        if (!$quotation->is_quotation) {
            return back()->with('error', 'This is not a quotation.');
        }

        try {
            $invoice = $quotation->convertToInvoice();
            
            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Quotation converted to invoice successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error converting quotation: ' . $e->getMessage());
        }
    }

    public function acceptQuotation(Invoice $quotation)
    {
        $this->authorize('update', $quotation);
        
        if (!$quotation->is_quotation) {
            return back()->with('error', 'This is not a quotation.');
        }

        $quotation->markAsAccepted();
        
        return back()->with('success', 'Quotation accepted successfully!');
    }

    // UPDATE: Modified validation method
    private function validateInvoice(Request $request, $invoiceId = null): array
    {
        $isQuotation = $request->boolean('is_quotation');
        
        $rules = [
            'client_id' => ['required', 'exists:clients,id'],
            'issue_date' => ['required', 'date'],
            'payment_terms' => ['required', 'string', 'max:255'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];

        // Different validation for quotation vs invoice
        if ($isQuotation) {
            $rules['valid_until'] = ['required', 'date', 'after:issue_date'];
        } else {
            $rules['due_date'] = ['required', 'date', 'after_or_equal:issue_date'];
        }

        return $request->validate($rules, [
            'client_id.required' => 'Please select a client.',
            'client_id.exists' => 'Selected client is invalid.',
            'issue_date.required' => 'Issue date is required.',
            'due_date.required' => 'Due date is required.',
            'due_date.after_or_equal' => 'Due date must be on or after issue date.',
            'valid_until.required' => 'Valid until date is required for quotations.',
            'valid_until.after' => 'Valid until date must be after issue date.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.description.required' => 'Item description is required.',
            'items.*.quantity.required' => 'Item quantity is required.',
            'items.*.quantity.min' => 'Item quantity must be greater than 0.',
            'items.*.unit_price.required' => 'Item unit price is required.',
            'items.*.unit_price.min' => 'Item unit price cannot be negative.',
        ]);
    }

    // UPDATE: Modified status counts method
    private function getStatusCounts($type = 'invoice'): array
    {
        $company = auth()->user()->company;
        
        if ($type === 'quotation') {
            return [
                'all' => $company->quotations()->count(),
                'draft' => $company->quotations()->byStatus('draft')->count(),
                'sent' => $company->quotations()->byStatus('sent')->count(),
                'accepted' => $company->quotations()->byStatus('accepted')->count(),
                'expired' => $company->quotations()->where('valid_until', '<', now())->whereNotIn('status', ['accepted', 'cancelled'])->count(),
            ];
        } else {
            return [
                'all' => $company->invoices()->count(),
                'draft' => $company->invoices()->byStatus('draft')->count(),
                'sent' => $company->invoices()->byStatus('sent')->count(),
                'paid' => $company->invoices()->byStatus('paid')->count(),
                'overdue' => $company->invoices()->overdue()->count(),
            ];
        }
    }
}
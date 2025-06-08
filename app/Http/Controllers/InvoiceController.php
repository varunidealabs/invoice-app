<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Resources\InvoiceResource;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');
        $type = $request->get('type'); // Can be 'invoice', 'quotation', or null (all)
        
        $company = auth()->user()->company;
        $query = $company->allDocuments()->with(['client', 'payments']);
        
        // Filter by document type if specified
        if ($type === 'quotation') {
            $query->quotations();
        } elseif ($type === 'invoice') {
            $query->invoices();
        }
        // If $type is null, show both invoices and quotations
        
        // Apply status filters
        $query->when($status, function ($query, $status) use ($type) {
            if ($status === 'overdue') {
                $query->overdue(); // Only applies to invoices
            } elseif ($status === 'expired') {
                $query->where('is_quotation', true)
                      ->where('valid_until', '<', now())
                      ->whereNotIn('status', ['accepted', 'cancelled']);
            } elseif ($status === 'paid') {
                // Show both paid invoices and accepted quotations
                $query->where(function($q) {
                    $q->where(function($sub) {
                        $sub->where('is_quotation', false)->where('status', 'paid');
                    })->orWhere(function($sub) {
                        $sub->where('is_quotation', true)->where('status', 'accepted');
                    });
                });
            } else {
                $query->byStatus($status);
            }
        });
        
        // Apply search filters
        $query->when($search, function ($query, $search) {
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('invoice_number', 'like', "%{$search}%");
        });
        
        $invoices = $query->latest('created_at')->paginate(15);
        $statusCounts = $this->getStatusCounts();

        // NEW: Check if it's an API request
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Documents retrieved successfully',
                'data' => InvoiceResource::collection($invoices->items()),
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                ]
            ]);
        }
        
        // EXISTING: Web response (keep unchanged)
        return view('invoices.index', compact('invoices', 'status', 'search', 'statusCounts', 'type'));
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        $invoice->load(['client', 'items', 'payments']);
        
        // NEW: Check if it's an API request
        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => 'Document retrieved successfully',
                'data' => new InvoiceResource($invoice)
            ]);
        }
        
        // EXISTING: Web response (keep unchanged)
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
        $type = $request->get('type', 'invoice');
        $isQuotation = $type === 'quotation';

        return view('invoices.create', compact('clients', 'selectedClientId', 'isQuotation'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateInvoice($request);
        $isQuotation = $request->boolean('is_quotation');
        
        try {
            $invoice = DB::transaction(function () use ($validated, $isQuotation) {
                $company = auth()->user()->company;
                
                $invoice = Invoice::create([
                    'company_id' => $company->id,
                    'client_id' => $validated['client_id'],
                    'invoice_number' => $isQuotation ? 
                        $company->getNextQuotationNumber() : 
                        $company->getNextInvoiceNumber(),
                    'is_quotation' => $isQuotation,
                    'issue_date' => $validated['issue_date'],
                    'due_date' => $isQuotation ? null : $validated['due_date'],
                    'valid_until' => $isQuotation ? $validated['valid_until'] : null,
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

            // NEW: Check if it's an API request
            if ($request->expectsJson() || $request->is('api/*')) {
                $invoice->load(['client', 'items', 'company']);
                return response()->json([
                    'message' => ($isQuotation ? 'Quotation' : 'Invoice') . ' created successfully',
                    'data' => new InvoiceResource($invoice)
                ], 201);
            }

            // EXISTING: Web response (keep unchanged)
            $type = $isQuotation ? 'quotation' : 'invoice';
            $message = $isQuotation ? 'Quotation created successfully!' : 'Invoice created successfully!';

            return redirect()->route('invoices.show', [$invoice, 'type' => $type])
                ->with('success', $message);
                
        } catch (\Exception $e) {
            \Log::error('Document creation failed: ' . $e->getMessage());
            
            // NEW: API error response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Error creating document',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            // EXISTING: Web error response
            return back()->withInput()
                ->with('error', 'Error creating document. Please try again.');
        }
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        if ($invoice->status === 'paid' || ($invoice->is_quotation && $invoice->status === 'accepted')) {
            $documentType = $invoice->is_quotation ? 'quotations' : 'invoices';
            return redirect()->route('invoices.show', $invoice)
                ->with('error', "Cannot edit {$documentType} that have been paid/accepted.");
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
        
        if ($invoice->status === 'paid' || ($invoice->is_quotation && $invoice->status === 'accepted')) {
            $documentType = $invoice->is_quotation ? 'quotations' : 'invoices';
            
            // NEW: API error response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => "Cannot edit {$documentType} that have been paid/accepted."
                ], 422);
            }
            
            // EXISTING: Web error response
            return redirect()->route('invoices.show', $invoice)
                ->with('error', "Cannot edit {$documentType} that have been paid/accepted.");
        }

        $validated = $this->validateInvoice($request, $invoice->id);
        
        try {
            DB::transaction(function () use ($invoice, $validated) {
                $updateData = [
                    'client_id' => $validated['client_id'],
                    'issue_date' => $validated['issue_date'],
                    'payment_terms' => $validated['payment_terms'],
                    'notes' => $validated['notes'] ?? null,
                    'terms' => $validated['terms'] ?? null,
                    'tax_rate' => $validated['tax_rate'] ?? 0,
                ];

                // Add appropriate date field based on document type
                if ($invoice->is_quotation) {
                    $updateData['valid_until'] = $validated['valid_until'];
                } else {
                    $updateData['due_date'] = $validated['due_date'];
                }

                $invoice->update($updateData);

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

            $documentType = $invoice->is_quotation ? 'Quotation' : 'Invoice';
            
            // NEW: Check if it's an API request
            if (request()->expectsJson() || request()->is('api/*')) {
                $invoice->load(['client', 'items', 'company']);
                return response()->json([
                    'message' => "{$documentType} updated successfully",
                    'data' => new InvoiceResource($invoice)
                ]);
            }
            
            // EXISTING: Web response
            return redirect()->route('invoices.show', $invoice)
                ->with('success', "{$documentType} updated successfully!");
        } catch (\Exception $e) {
            \Log::error('Document update failed: ' . $e->getMessage());
            
            // NEW: API error response
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'message' => 'Error updating document',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            // EXISTING: Web error response
            return back()->withInput()
                ->with('error', 'Error updating document. Please try again.');
        }
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);
        
        if ($invoice->status === 'paid' || ($invoice->is_quotation && $invoice->status === 'accepted')) {
            $documentType = $invoice->is_quotation ? 'quotations' : 'invoices';
            
            // NEW: API error response
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'message' => "Cannot delete {$documentType} that have been paid/accepted."
                ], 422);
            }
            
            // EXISTING: Web error response
            return back()->with('error', "Cannot delete {$documentType} that have been paid/accepted.");
        }

        $documentType = $invoice->is_quotation ? 'Quotation' : 'Invoice';
        $invoice->delete();

        // NEW: Check if it's an API request
        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => "{$documentType} deleted successfully"
            ]);
        }
        
        // EXISTING: Web response
        return redirect()->route('invoices.index')
            ->with('success', "{$documentType} deleted successfully!");
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

    public function markAsSent(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        $invoice->markAsSent();
        
        $documentType = $invoice->is_quotation ? 'Quotation' : 'Invoice';
        
        // NEW: Check if it's an API request
        if (request()->expectsJson() || request()->is('api/*')) {
            $invoice->load(['client', 'items', 'company']);
            return response()->json([
                'message' => "{$documentType} marked as sent",
                'data' => new InvoiceResource($invoice)
            ]);
        }
        
        // EXISTING: Web response
        return back()->with('success', "{$documentType} marked as sent!");
    }

    public function markAsPaid(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        if ($invoice->is_quotation) {
            $invoice->markAsAccepted();
            $message = 'Quotation marked as accepted';
        } else {
            $invoice->markAsPaid();
            $message = 'Invoice marked as paid';
        }
        
        // NEW: Check if it's an API request
        if (request()->expectsJson() || request()->is('api/*')) {
            $invoice->load(['client', 'items', 'company']);
            return response()->json([
                'message' => $message,
                'data' => new InvoiceResource($invoice)
            ]);
        }
        
        // EXISTING: Web response
        return back()->with('success', $message . '!');
    }

    public function convertToInvoice(Invoice $quotation)
    {
        $this->authorize('update', $quotation);
        
        if (!$quotation->is_quotation) {
            // NEW: API error response
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'message' => 'This is not a quotation'
                ], 422);
            }
            
            // EXISTING: Web error response
            return back()->with('error', 'This is not a quotation.');
        }

        try {
            $invoice = $quotation->convertToInvoice();
            
            // NEW: Check if it's an API request
            if (request()->expectsJson() || request()->is('api/*')) {
                $invoice->load(['client', 'items', 'company']);
                return response()->json([
                    'message' => 'Quotation converted to invoice successfully',
                    'data' => new InvoiceResource($invoice)
                ], 201);
            }
            
            // EXISTING: Web response
            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Quotation converted to invoice successfully!');
        } catch (\Exception $e) {
            // NEW: API error response
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'message' => 'Error converting quotation',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            // EXISTING: Web error response
            return back()->with('error', 'Error converting quotation: ' . $e->getMessage());
        }
    }

    public function acceptQuotation(Invoice $quotation)
    {
        $this->authorize('update', $quotation);
        
        if (!$quotation->is_quotation) {
            // NEW: API error response
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'message' => 'This is not a quotation'
                ], 422);
            }
            
            // EXISTING: Web error response
            return back()->with('error', 'This is not a quotation.');
        }

        $quotation->markAsAccepted();
        
        // NEW: Check if it's an API request
        if (request()->expectsJson() || request()->is('api/*')) {
            $quotation->load(['client', 'items', 'company']);
            return response()->json([
                'message' => 'Quotation accepted successfully',
                'data' => new InvoiceResource($quotation)
            ]);
        }
        
        // EXISTING: Web response
        return back()->with('success', 'Quotation accepted successfully!');
    }

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

    private function getStatusCounts(): array
    {
        $company = auth()->user()->company;
        $cacheKey = "invoice_status_counts_{$company->id}";
        
        return Cache::tags(['invoices', 'quotations'])
            ->remember($cacheKey, 600, function() use ($company) {
                Log::info("Cache MISS: Regenerating status counts for company {$company->id}");
                
                return [
                    'all_invoices' => $company->invoices()->count(),
                    'draft_invoices' => $company->invoices()->byStatus('draft')->count(),
                    'sent_invoices' => $company->invoices()->byStatus('sent')->count(),
                    'paid_invoices' => $company->invoices()->byStatus('paid')->count(),
                    'overdue_invoices' => $company->invoices()->overdue()->count(),
                    
                    'all_quotations' => $company->quotations()->count(),
                    'draft_quotations' => $company->quotations()->byStatus('draft')->count(),
                    'sent_quotations' => $company->quotations()->byStatus('sent')->count(),
                    'accepted_quotations' => $company->quotations()->byStatus('accepted')->count(),
                    'expired_quotations' => $company->quotations()
                        ->where('valid_until', '<', now())
                        ->whereNotIn('status', ['accepted', 'cancelled'])
                        ->count(),
                ];
            });
    }
}
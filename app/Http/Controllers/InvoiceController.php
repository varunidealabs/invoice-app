<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Http\Resources\InvoiceResource;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');
        $type = $request->get('type'); // Can be 'invoice', 'quotation', or null (all)
        
        $company = auth()->user()->company;
        
        // Use cache only when no search or filters are applied
        $useCache = !$search && !$status && !$type;
        
        if ($useCache) {
            // Use cached data for basic listing
            $invoices = CacheService::getAllDocuments($company->id);
            
            // Convert to paginator
            $perPage = 15;
            $currentPage = request()->get('page', 1);
            $items = $invoices->forPage($currentPage, $perPage);
            
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $invoices->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'pageName' => 'page']
            );
            
            $invoices = $paginator;
            Log::info("Used cached invoice list for company {$company->id}");
        } else {
            // Dynamic query for filtered results
            $query = $company->allDocuments()->with(['client', 'payments']);
            
            // Filter by document type if specified
            if ($type === 'quotation') {
                $query->quotations();
            } elseif ($type === 'invoice') {
                $query->invoices();
            }
            
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
            Log::info("Dynamic query used for invoice list with filters");
        }
        
        // Get cached status counts
        $statusCounts = CacheService::getStatusCounts($company->id);

        // API Response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Documents retrieved successfully',
                'data' => InvoiceResource::collection($invoices->items()),
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                ],
                'status_counts' => $statusCounts,
                'cache_used' => $useCache
            ]);
        }
        
        return view('invoices.index', compact('invoices', 'status', 'search', 'statusCounts', 'type'));
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        // Cache invoice details
        $cacheKey = "invoice_details_{$invoice->id}";
        $invoiceDetails = Cache::tags(['invoices', 'clients'])
            ->remember($cacheKey, 1800, function() use ($invoice) { // 30 minutes
                Log::info("Cache MISS: Loading invoice details for invoice {$invoice->id}");
                
                return $invoice->load(['client', 'items', 'payments', 'company']);
            });

        // API Response
        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => 'Document retrieved successfully',
                'data' => new InvoiceResource($invoiceDetails)
            ]);
        }
        
        return view('invoices.show', ['invoice' => $invoiceDetails]);
    }

    public function create(Request $request)
    {
        // Get cached clients list
        $companyId = auth()->user()->company->id;
        $clients = CacheService::getClientsList($companyId);

        if ($clients->isEmpty()) {
            $message = 'Please create a client first before creating a document.';
            
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 422);
            }
            
            return redirect()->route('clients.create')->with('info', $message);
        }

        $selectedClientId = $request->get('client');
        $type = $request->get('type', 'invoice');
        $isQuotation = $type === 'quotation';

        // API Response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Create form data',
                'data' => [
                    'clients' => $clients,
                    'type' => $type,
                    'is_quotation' => $isQuotation,
                    'selected_client_id' => $selectedClientId,
                    'payment_terms_options' => [
                        'Due on receipt' => 'Due on receipt',
                        'Net 15' => 'Net 15 days',
                        'Net 30' => 'Net 30 days',
                        'Net 45' => 'Net 45 days',
                        'Net 60' => 'Net 60 days'
                    ]
                ]
            ]);
        }

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

            // Cache will be automatically invalidated by observer
            Log::info("Invoice created: {$invoice->id}");

            // API Response
            if ($request->expectsJson() || $request->is('api/*')) {
                $invoice->load(['client', 'items', 'company']);
                return response()->json([
                    'message' => ($isQuotation ? 'Quotation' : 'Invoice') . ' created successfully',
                    'data' => new InvoiceResource($invoice)
                ], 201);
            }

            $type = $isQuotation ? 'quotation' : 'invoice';
            $message = $isQuotation ? 'Quotation created successfully!' : 'Invoice created successfully!';

            return redirect()->route('invoices.show', [$invoice, 'type' => $type])
                ->with('success', $message);
                
        } catch (\Exception $e) {
            Log::error('Document creation failed: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Error creating document',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()
                ->with('error', 'Error creating document. Please try again.');
        }
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        if ($invoice->status === 'paid' || ($invoice->is_quotation && $invoice->status === 'accepted')) {
            $documentType = $invoice->is_quotation ? 'quotations' : 'invoices';
            $message = "Cannot edit {$documentType} that have been paid/accepted.";
            
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json(['message' => $message], 422);
            }
            
            return redirect()->route('invoices.show', $invoice)->with('error', $message);
        }

        // Get cached clients list
        $companyId = auth()->user()->company->id;
        $clients = CacheService::getClientsList($companyId);

        $invoice->load('items');

        // API Response
        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => 'Edit form data',
                'data' => [
                    'invoice' => new InvoiceResource($invoice),
                    'clients' => $clients
                ]
            ]);
        }

        return view('invoices.edit', compact('invoice', 'clients'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        
        if ($invoice->status === 'paid' || ($invoice->is_quotation && $invoice->status === 'accepted')) {
            $documentType = $invoice->is_quotation ? 'quotations' : 'invoices';
            $message = "Cannot edit {$documentType} that have been paid/accepted.";
            
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 422);
            }
            
            return redirect()->route('invoices.show', $invoice)->with('error', $message);
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

            // Cache will be automatically invalidated by observer
            Log::info("Invoice updated: {$invoice->id}");

            $documentType = $invoice->is_quotation ? 'Quotation' : 'Invoice';
            
            if (request()->expectsJson() || request()->is('api/*')) {
                $invoice->load(['client', 'items', 'company']);
                return response()->json([
                    'message' => "{$documentType} updated successfully",
                    'data' => new InvoiceResource($invoice)
                ]);
            }
            
            return redirect()->route('invoices.show', $invoice)
                ->with('success', "{$documentType} updated successfully!");
                
        } catch (\Exception $e) {
            Log::error('Document update failed: ' . $e->getMessage());
            
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'message' => 'Error updating document',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()
                ->with('error', 'Error updating document. Please try again.');
        }
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);
        
        if ($invoice->status === 'paid' || ($invoice->is_quotation && $invoice->status === 'accepted')) {
            $documentType = $invoice->is_quotation ? 'quotations' : 'invoices';
            $message = "Cannot delete {$documentType} that have been paid/accepted.";
            
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json(['message' => $message], 422);
            }
            
            return back()->with('error', $message);
        }

        try {
            $documentType = $invoice->is_quotation ? 'Quotation' : 'Invoice';
            $invoice->delete();
            
            // Cache will be automatically invalidated by observer
            Log::info("Invoice deleted: {$invoice->id}");

            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'message' => "{$documentType} deleted successfully"
                ]);
            }
            
            return redirect()->route('invoices.index')
                ->with('success', "{$documentType} deleted successfully!");
                
        } catch (\Exception $e) {
            Log::error('Invoice deletion failed: ' . $e->getMessage());
            
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'message' => 'Error deleting document',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error deleting document. Please try again.');
        }
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
        
        // Invalidate cache for this specific invoice
        Cache::tags(['invoices'])->forget("invoice_details_{$invoice->id}");
        
        $documentType = $invoice->is_quotation ? 'Quotation' : 'Invoice';
        
        if (request()->expectsJson() || request()->is('api/*')) {
            $invoice->load(['client', 'items', 'company']);
            return response()->json([
                'message' => "{$documentType} marked as sent",
                'data' => new InvoiceResource($invoice)
            ]);
        }
        
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
        
        // Invalidate cache for this specific invoice
        Cache::tags(['invoices'])->forget("invoice_details_{$invoice->id}");
        
        if (request()->expectsJson() || request()->is('api/*')) {
            $invoice->load(['client', 'items', 'company']);
            return response()->json([
                'message' => $message,
                'data' => new InvoiceResource($invoice)
            ]);
        }
        
        return back()->with('success', $message . '!');
    }

    public function convertToInvoice(Invoice $quotation)
    {
        $this->authorize('update', $quotation);
        
        if (!$quotation->is_quotation) {
            $message = 'This is not a quotation.';
            
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json(['message' => $message], 422);
            }
            
            return back()->with('error', $message);
        }

        try {
            $invoice = $quotation->convertToInvoice();
            
            // Cache will be automatically invalidated by observer
            Log::info("Quotation {$quotation->id} converted to invoice {$invoice->id}");
            
            if (request()->expectsJson() || request()->is('api/*')) {
                $invoice->load(['client', 'items', 'company']);
                return response()->json([
                    'message' => 'Quotation converted to invoice successfully',
                    'data' => new InvoiceResource($invoice)
                ], 201);
            }
            
            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Quotation converted to invoice successfully!');
                
        } catch (\Exception $e) {
            Log::error('Quotation conversion failed: ' . $e->getMessage());
            
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'message' => 'Error converting quotation',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error converting quotation: ' . $e->getMessage());
        }
    }

    public function acceptQuotation(Invoice $quotation)
    {
        $this->authorize('update', $quotation);
        
        if (!$quotation->is_quotation) {
            $message = 'This is not a quotation.';
            
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json(['message' => $message], 422);
            }
            
            return back()->with('error', $message);
        }

        $quotation->markAsAccepted();
        
        // Invalidate cache for this specific quotation
        Cache::tags(['quotations'])->forget("invoice_details_{$quotation->id}");
        
        if (request()->expectsJson() || request()->is('api/*')) {
            $quotation->load(['client', 'items', 'company']);
            return response()->json([
                'message' => 'Quotation accepted successfully',
                'data' => new InvoiceResource($quotation)
            ]);
        }
        
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
}
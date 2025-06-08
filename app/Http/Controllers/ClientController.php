<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\CacheService;
use App\Http\Resources\ClientResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $companyId = auth()->user()->company->id;

        if (!$search) {
            // Use cached data for listing without search
            $clients = CacheService::getClientsList($companyId);
            
            // Convert collection to paginator for consistency
            $perPage = 15;
            $currentPage = request()->get('page', 1);
            $items = $clients->forPage($currentPage, $perPage);
            
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $clients->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'pageName' => 'page']
            );
            
            $clients = $paginator;
        } else {
            // Don't cache search results - they're dynamic
            $clients = auth()->user()->company->clients()
                ->search($search)
                ->withCount('invoices')
                ->latest()
                ->paginate(15);
                
            Log::info("Search performed for: {$search}");
        }

        // API Response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Clients retrieved successfully',
                'data' => ClientResource::collection($clients->items()),
                'pagination' => [
                    'current_page' => $clients->currentPage(),
                    'last_page' => $clients->lastPage(),
                    'per_page' => $clients->perPage(),
                    'total' => $clients->total(),
                ],
                'cache_used' => !$search
            ]);
        }

        return view('clients.index', compact('clients', 'search'));
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);
        
        // Cache client details with invoices
        $cacheKey = "client_details_{$client->id}";
        $clientDetails = Cache::tags(['clients', 'invoices'])
            ->remember($cacheKey, 900, function() use ($client) { // 15 minutes
                Log::info("Cache MISS: Loading client details for client {$client->id}");
                
                return [
                    'client' => $client->load(['company']),
                    'recent_invoices' => $client->invoices()
                        ->with('payments')
                        ->latest()
                        ->take(10)
                        ->get(),
                    'invoice_stats' => [
                        'total_invoices' => $client->invoices()->count(),
                        'total_paid' => $client->invoices()->where('status', 'paid')->sum('total'),
                        'total_pending' => $client->invoices()->whereIn('status', ['sent', 'viewed'])->sum('total'),
                        'overdue_count' => $client->invoices()->where('due_date', '<', now())->where('status', '!=', 'paid')->count(),
                    ]
                ];
            });

        $recentInvoices = $clientDetails['recent_invoices'];
        $client = $clientDetails['client'];

        // API Response
        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => 'Client retrieved successfully',
                'data' => new ClientResource($client),
                'recent_invoices' => $recentInvoices,
                'stats' => $clientDetails['invoice_stats']
            ]);
        }

        return view('clients.show', compact('client', 'recentInvoices'));
    }

    public function create()
    {
        // API Response
        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => 'Create client form data',
                'data' => [
                    'countries' => ['India', 'United States', 'United Kingdom', 'Canada', 'Australia'],
                    'states' => [
                        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 
                        'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 
                        'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 
                        'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 
                        'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal'
                    ]
                ]
            ]);
        }

        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateClient($request);
        $validated['company_id'] = auth()->user()->company->id;
        
        try {
            $client = Client::create($validated);
            
            // Cache will be automatically invalidated by observer
            Log::info("Client created: {$client->id}");

            // API Response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Client created successfully',
                    'data' => new ClientResource($client)
                ], 201);
            }
            
            return redirect()->route('clients.show', $client)
                ->with('success', 'Client created successfully!');
                
        } catch (\Exception $e) {
            Log::error('Client creation failed: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Error creating client',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()
                ->with('error', 'Error creating client. Please try again.');
        }
    }

    public function edit(Client $client)
    {
        $this->authorize('update', $client);
        
        // API Response
        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => 'Edit client data',
                'data' => new ClientResource($client)
            ]);
        }
        
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);
        
        $validated = $this->validateClient($request, $client->id);
        
        try {
            $client->update($validated);
            
            // Cache will be automatically invalidated by observer
            Log::info("Client updated: {$client->id}");

            // API Response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Client updated successfully',
                    'data' => new ClientResource($client)
                ]);
            }

            return redirect()->route('clients.show', $client)
                ->with('success', 'Client updated successfully!');
                
        } catch (\Exception $e) {
            Log::error('Client update failed: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Error updating client',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()
                ->with('error', 'Error updating client. Please try again.');
        }
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);
        
        // Check if client has invoices
        $invoiceCount = Cache::tags(['invoices'])
            ->remember("client_invoice_count_{$client->id}", 300, function() use ($client) {
                return $client->invoices()->count();
            });
        
        if ($invoiceCount > 0) {
            $message = 'Cannot delete client with existing invoices.';
            
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json(['message' => $message], 422);
            }
            
            return back()->with('error', $message);
        }

        try {
            $client->delete();
            
            // Cache will be automatically invalidated by observer
            Log::info("Client deleted: {$client->id}");

            // API Response
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'message' => 'Client deleted successfully'
                ]);
            }

            return redirect()->route('clients.index')
                ->with('success', 'Client deleted successfully!');
                
        } catch (\Exception $e) {
            Log::error('Client deletion failed: ' . $e->getMessage());
            
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'message' => 'Error deleting client',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error deleting client. Please try again.');
        }
    }

    private function validateClient(Request $request, $clientId = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable', 
                'email', 
                'max:255',
                Rule::unique('clients')->where(function ($query) {
                    return $query->where('company_id', auth()->user()->company->id);
                })->ignore($clientId)
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
        ];

        return $request->validate($rules, [
            'name.required' => 'Client name is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'A client with this email already exists.',
        ]);
    }
}
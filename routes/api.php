<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use App\Models\User;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientController;

/*
|--------------------------------------------------------------------------
| API Routes - InvoiceApp REST API v1 (AUTHENTICATION ENABLED)
|--------------------------------------------------------------------------
*/

// Configure Rate Limiting
RateLimiter::for('auth', function (Request $request) {
    return [
        Limit::perMinute(5)->by($request->ip()),
        Limit::perHour(20)->by($request->ip()),
        Limit::perDay(100)->by($request->ip()),
    ];
});

RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('heavy', function (Request $request) {
    return [
        Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()),
        Limit::perHour(100)->by($request->user()?->id ?: $request->ip()),
    ];
});

RateLimiter::for('chat', function (Request $request) {
    return [
        Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()),
        Limit::perHour(300)->by($request->user()?->id ?: $request->ip()),
    ];
});

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// API Health Check
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'service' => 'InvoiceApp API',
        'version' => '2.0.0',
        'timestamp' => now()->toISOString(),
        'features' => [
            'authentication' => 'enabled',
            'ai_chat' => 'function_calling',
            'intelligent_defaults' => 'enabled'
        ]
    ]);
});

// API Documentation
Route::get('/docs', function () {
    return response()->json([
        'api_name' => 'InvoiceApp REST API',
        'version' => '2.0.0',
        'description' => 'AI-Powered Professional Invoicing & Quotation Management API',
        'features' => [
            'ai_chat_with_function_calling' => 'Create invoices/quotations through natural language',
            'intelligent_client_lookup' => 'Reference clients by name instead of ID',
            'smart_defaults' => 'Auto-complete optional fields based on context',
            'company_context_awareness' => 'Leverages user company data for better experience'
        ],
        'authentication' => [
            'type' => 'Bearer Token (Sanctum)',
            'login_endpoint' => '/api/auth/login',
            'header_format' => 'Authorization: Bearer {token}'
        ],
        'new_features' => [
            'POST /api/chat' => 'Natural language invoice/quotation creation with function calling',
            'Smart client lookup' => 'Use client names instead of IDs',
            'Context-aware responses' => 'Leverages company data for intelligent suggestions'
        ]
    ]);
});

/*
|--------------------------------------------------------------------------
| Authentication Routes (Rate Limited)
|--------------------------------------------------------------------------
*/

Route::middleware('throttle:auth')->group(function () {
    
    /**
     * Enhanced Login with Company Context
     */
    Route::post('/auth/login', function (Request $request) {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:6'
            ]);

            $user = User::where('email', $request->email)->first();
            
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid credentials',
                    'error_code' => 'INVALID_CREDENTIALS'
                ], 401);
            }

            if (!$user->hasCompany()) {
                return response()->json([
                    'message' => 'Company setup required. Please complete your company profile first.',
                    'error_code' => 'COMPANY_SETUP_REQUIRED',
                    'setup_url' => url('/company/setup')
                ], 422);
            }

            // Create token with extended expiration for better UX
            $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;
            
            // Load user with company and recent data for context
            $userWithContext = $user->load([
                'company',
                'company.clients' => function($query) {
                    $query->latest()->take(10);
                }
            ]);
            
            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 30 * 24 * 60 * 60,
                'user' => $userWithContext,
                'company_stats' => [
                    'total_clients' => $user->company->clients()->count(),
                    'total_invoices' => $user->company->invoices()->count(),
                    'total_quotations' => $user->company->quotations()->count(),
                ],
                'api_features' => [
                    'ai_chat_enabled' => true,
                    'function_calling' => true,
                    'intelligent_defaults' => true
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Login failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Login failed. Please try again.',
                'error_code' => 'LOGIN_ERROR'
            ], 500);
        }
    });
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Authentication Required)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | User Management (Standard Rate Limit)
    |--------------------------------------------------------------------------
    */
    Route::middleware('throttle:api')->group(function () {
        
        /**
         * Get current authenticated user with company context
         */
        Route::get('/user', function (Request $request) {
            $user = $request->user()->load([
                'company',
                'company.clients' => function($query) {
                    $query->latest()->take(20);
                }
            ]);
            
            return response()->json([
                'message' => 'User retrieved successfully',
                'data' => $user,
                'context' => [
                    'recent_clients_count' => $user->company->clients()->count(),
                    'recent_invoices_count' => $user->company->invoices()->count(),
                    'recent_quotations_count' => $user->company->quotations()->count(),
                ]
            ]);
        });
        
        /**
         * Logout and revoke current token
         */
        Route::post('/auth/logout', function (Request $request) {
            try {
                $request->user()->currentAccessToken()->delete();
                return response()->json([
                    'message' => 'Logged out successfully'
                ]);
            } catch (\Exception $e) {
                \Log::error('Logout failed: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Logout failed. Please try again.'
                ], 500);
            }
        });

        /**
         * Logout from all devices
         */
        Route::post('/auth/logout-all', function (Request $request) {
            try {
                $request->user()->tokens()->delete();
                return response()->json([
                    'message' => 'Logged out from all devices successfully'
                ]);
            } catch (\Exception $e) {
                \Log::error('Logout all failed: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Logout failed. Please try again.'
                ], 500);
            }
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | Invoice & Quotation Management (Standard Rate Limit)
    |--------------------------------------------------------------------------
    */
    Route::middleware('throttle:api')->group(function () {
        
        /**
         * Invoice and Quotation CRUD Operations
         */
        Route::apiResource('invoices', InvoiceController::class)->names([
            'index' => 'api.invoices.index',
            'store' => 'api.invoices.store', 
            'show' => 'api.invoices.show',
            'update' => 'api.invoices.update',
            'destroy' => 'api.invoices.destroy'
        ]);
        
        /**
         * Invoice Status Management
         */
        Route::patch('invoices/{invoice}/mark-sent', [InvoiceController::class, 'markAsSent'])
            ->name('api.invoices.mark-sent');
            
        Route::patch('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])
            ->name('api.invoices.mark-paid');
        
        /**
         * Quotation-specific Actions
         */
        Route::patch('quotations/{invoice}/accept', [InvoiceController::class, 'acceptQuotation'])
            ->name('api.quotations.accept');
            
        Route::post('quotations/{invoice}/convert', [InvoiceController::class, 'convertToInvoice'])
            ->name('api.quotations.convert');
        
        /**
         * Client Management
         */
        Route::apiResource('clients', ClientController::class)->names([
            'index' => 'api.clients.index',
            'store' => 'api.clients.store',
            'show' => 'api.clients.show', 
            'update' => 'api.clients.update',
            'destroy' => 'api.clients.destroy'
        ]);
    });
    
    /*
    |--------------------------------------------------------------------------
    | Heavy Operations (Stricter Rate Limit)
    |--------------------------------------------------------------------------
    */
    Route::middleware('throttle:heavy')->group(function () {
        
        /**
         * PDF Operations
         */
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'viewPdf'])
            ->name('api.invoices.pdf');
            
        Route::get('invoices/{invoice}/download', [InvoiceController::class, 'downloadPdf'])
            ->name('api.invoices.download');
    });
    
    /*
    |--------------------------------------------------------------------------
    | AI Chat Features (Special Rate Limit) - ENHANCED WITH FUNCTION CALLING
    |--------------------------------------------------------------------------
    */
    Route::middleware('throttle:chat')->group(function () {
        
        /**
         * Enhanced AI-Powered Invoice/Quotation Creation via Chat
         * Now supports function calling and intelligent client lookup
         */
        Route::post('/chat', [\App\Http\Controllers\ChatGptController::class, 'chat'])
            ->name('api.chat.process');
        
        /**
         * Voice Transcription for Chat
         */
        Route::post('/chat/transcribe', [\App\Http\Controllers\ChatGptController::class, 'transcribe'])
            ->name('api.chat.transcribe');
            
        /**
         * Get User Context for Chat (clients, recent invoices, etc.)
         */
        Route::get('/chat/context', function (Request $request) {
            $user = $request->user();
            $company = $user->company;
            
            return response()->json([
                'message' => 'Chat context retrieved successfully',
                'data' => [
                    'company' => [
                        'name' => $company->company_name,
                        'currency' => $company->currency,
                        'currency_symbol' => $company->currency_symbol,
                        'default_payment_terms' => $company->default_payment_terms,
                    ],
                    'clients' => $company->clients()->latest()->take(50)->get(['id', 'name', 'email', 'contact_person']),
                    'recent_invoices' => $company->allDocuments()
                        ->with('client:id,name')
                        ->latest()
                        ->take(20)
                        ->get(['id', 'invoice_number', 'client_id', 'is_quotation', 'total', 'status']),
                    'common_items' => $company->allDocuments()
                        ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
                        ->select('invoice_items.description', 'invoice_items.unit_price')
                        ->groupBy('invoice_items.description', 'invoice_items.unit_price')
                        ->orderByRaw('COUNT(*) DESC')
                        ->take(10)
                        ->get(),
                    'statistics' => [
                        'total_clients' => $company->clients()->count(),
                        'total_invoices' => $company->invoices()->count(),
                        'total_quotations' => $company->quotations()->count(),
                        'common_tax_rate' => $company->allDocuments()
                            ->groupBy('tax_rate')
                            ->orderByRaw('COUNT(*) DESC')
                            ->value('tax_rate') ?? 0,
                    ]
                ]
            ]);
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | Analytics & Reporting (Standard Rate Limit)
    |--------------------------------------------------------------------------
    */
    Route::middleware('throttle:api')->group(function () {
        
        /**
         * Enhanced Dashboard Statistics
         */
        Route::get('/dashboard/stats', function (Request $request) {
            $user = $request->user();
            $company = $user->company;
            
            $stats = [
                'overview' => [
                    'total_clients' => $company->clients()->count(),
                    'total_invoices' => $company->invoices()->count(),
                    'total_quotations' => $company->quotations()->count(),
                    'total_revenue' => $company->invoices()->where('status', 'paid')->sum('total'),
                    'pending_amount' => $company->invoices()->whereIn('status', ['sent', 'viewed'])->sum('total'),
                ],
                'status_breakdown' => [
                    'draft_invoices' => $company->invoices()->where('status', 'draft')->count(),
                    'sent_invoices' => $company->invoices()->where('status', 'sent')->count(),
                    'paid_invoices' => $company->invoices()->where('status', 'paid')->count(),
                    'overdue_invoices' => $company->invoices()->overdue()->count(),
                    'draft_quotations' => $company->quotations()->where('status', 'draft')->count(),
                    'sent_quotations' => $company->quotations()->where('status', 'sent')->count(),
                    'accepted_quotations' => $company->quotations()->where('status', 'accepted')->count(),
                ],
                'trends' => [
                    'this_month_revenue' => $company->invoices()
                        ->where('status', 'paid')
                        ->whereMonth('paid_at', now()->month)
                        ->sum('total'),
                    'this_month_invoices' => $company->invoices()
                        ->whereMonth('created_at', now()->month)
                        ->count(),
                    'avg_invoice_value' => $company->invoices()->avg('total'),
                ],
                'currency' => [
                    'code' => $company->currency,
                    'symbol' => $company->currency_symbol
                ],
                'generated_at' => now()->toISOString()
            ];
            
            return response()->json([
                'message' => 'Dashboard statistics retrieved successfully',
                'data' => $stats
            ]);
        });
        
        /**
         * Recent Activity with Enhanced Context
         */
        Route::get('/dashboard/recent', function (Request $request) {
            $user = $request->user();
            $company = $user->company;
            
            $recentInvoices = $company->invoices()
                ->with(['client:id,name,email', 'items:id,invoice_id,description,total'])
                ->latest()
                ->take(10)
                ->get();
                
            $recentQuotations = $company->quotations()
                ->with(['client:id,name,email', 'items:id,invoice_id,description,total'])
                ->latest()
                ->take(10)
                ->get();
                
            $recentClients = $company->clients()
                ->withCount('invoices')
                ->latest()
                ->take(10)
                ->get();
            
            return response()->json([
                'message' => 'Recent activity retrieved successfully',
                'data' => [
                    'recent_invoices' => $recentInvoices,
                    'recent_quotations' => $recentQuotations,
                    'recent_clients' => $recentClients,
                    'quick_actions' => [
                        'most_billed_client' => $company->clients()
                            ->withSum('invoices', 'total')
                            ->orderBy('invoices_sum_total', 'desc')
                            ->first(['id', 'name', 'invoices_sum_total']),
                        'overdue_count' => $company->invoices()->overdue()->count(),
                        'pending_quotations' => $company->quotations()->where('status', 'sent')->count(),
                    ]
                ]
            ]);
        });
        
        /**
         * Smart Suggestions for Chat
         */
        Route::get('/suggestions', function (Request $request) {
            $user = $request->user();
            $company = $user->company;
            
            // Get common patterns for intelligent suggestions
            $suggestions = [
                'common_clients' => $company->clients()
                    ->withCount('invoices')
                    ->orderBy('invoices_count', 'desc')
                    ->take(5)
                    ->get(['id', 'name']),
                'common_items' => $company->allDocuments()
                    ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
                    ->select('invoice_items.description', 'invoice_items.unit_price')
                    ->groupBy('invoice_items.description', 'invoice_items.unit_price')
                    ->orderByRaw('COUNT(*) DESC')
                    ->take(10)
                    ->get(),
                'recent_amounts' => $company->allDocuments()
                    ->select('total')
                    ->latest()
                    ->take(10)
                    ->pluck('total')
                    ->unique()
                    ->values(),
                'quick_templates' => [
                    'consultation' => ['description' => 'Consultation Services', 'unit_price' => 150],
                    'web_development' => ['description' => 'Web Development', 'unit_price' => 5000],
                    'design' => ['description' => 'Design Services', 'unit_price' => 2500],
                    'maintenance' => ['description' => 'Monthly Maintenance', 'unit_price' => 500],
                ]
            ];
            
            return response()->json([
                'message' => 'Suggestions retrieved successfully',
                'data' => $suggestions
            ]);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Legacy ChatGPT Routes (Backward Compatibility)
|--------------------------------------------------------------------------
*/

/**
 * LEGACY: Direct Invoice Creation from JSON (kept for backward compatibility)
 * Note: New implementations should use the enhanced /chat endpoint
 */
Route::post('/chatgpt/create-invoice', function (Request $request) {
    try {
        // This endpoint now requires authentication
        $user = auth('sanctum')->user();
        if (!$user || !$user->company) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required. Please use /api/auth/login first.',
                'error_code' => 'AUTHENTICATION_REQUIRED'
            ], 401);
        }

        // Validate the JSON structure
        $validated = $request->validate([
            'client_id' => 'required|integer',
            'is_quotation' => 'required|boolean',
            'issue_date' => 'nullable|string',
            'due_date' => 'nullable|string', 
            'payment_terms' => 'nullable|string',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);

        // Set defaults for empty fields
        if (empty($validated['payment_terms'])) {
            $validated['payment_terms'] = $user->company->default_payment_terms;
        }
        
        if (empty($validated['issue_date'])) {
            $validated['issue_date'] = now()->format('Y-m-d');
        }
        
        if (empty($validated['due_date'])) {
            $validated['due_date'] = now()->addDays(30)->format('Y-m-d');
        }

        // Create invoice using authenticated user's company
        $invoice = \DB::transaction(function () use ($validated, $user) {
            $company = $user->company;
            $isQuotation = $validated['is_quotation'];
            
            $invoice = \App\Models\Invoice::create([
                'company_id' => $company->id,
                'client_id' => $validated['client_id'],
                'invoice_number' => $isQuotation ? 
                    $company->getNextQuotationNumber() : 
                    $company->getNextInvoiceNumber(),
                'is_quotation' => $isQuotation,
                'issue_date' => $validated['issue_date'],
                'due_date' => $isQuotation ? null : $validated['due_date'],
                'valid_until' => $isQuotation ? $validated['due_date'] : null,
                'payment_terms' => $validated['payment_terms'],
                'notes' => $validated['notes'] ?? '',
                'terms' => $validated['terms'] ?? '',
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
            return $invoice->load(['client', 'items', 'company']);
        });

        return response()->json([
            'success' => true,
            'message' => $validated['is_quotation'] ? 'Quotation created successfully!' : 'Invoice created successfully!',
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'type' => $invoice->is_quotation ? 'quotation' : 'invoice',
            'total_amount' => $invoice->total,
            'pdf_url' => url("/api/invoices/{$invoice->id}/pdf"),
            'download_url' => url("/api/invoices/{$invoice->id}/download"),
            'view_url' => url("/invoices/{$invoice->id}")
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid JSON structure',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Legacy Invoice Creation Error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to create invoice: ' . $e->getMessage()
        ], 500);
    }
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Error Handling & Rate Limit Responses
|--------------------------------------------------------------------------
*/

// Global error handler for API routes
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint not found',
        'error_code' => 'ENDPOINT_NOT_FOUND',
        'available_endpoints' => url('/api/docs'),
        'suggestion' => 'Check the API documentation for available endpoints'
    ], 404);
});

/*
|--------------------------------------------------------------------------
| Rate Limit Information Endpoint
|--------------------------------------------------------------------------
*/

Route::get('/rate-limits', function (Request $request) {
    $user = $request->user();
    $identifier = $user ? $user->id : $request->ip();
    
    return response()->json([
        'rate_limits' => [
            'authentication' => [
                'limit' => '5 per minute, 20 per hour, 100 per day',
                'remaining_minute' => RateLimiter::remaining('auth:' . $request->ip(), 5),
                'reset_time' => now()->addMinute()->timestamp
            ],
            'general_api' => [
                'limit' => '60 per minute',
                'remaining' => $user ? RateLimiter::remaining('api:' . $user->id, 60) : 'Login required',
                'reset_time' => now()->addMinute()->timestamp
            ],
            'heavy_operations' => [
                'limit' => '10 per minute, 100 per hour',
                'remaining_minute' => $user ? RateLimiter::remaining('heavy:' . $user->id, 10) : 'Login required',
                'remaining_hour' => $user ? RateLimiter::remaining('heavy:' . $user->id . ':hour', 100) : 'Login required',
            ],
            'ai_chat' => [
                'limit' => '30 per minute, 300 per hour (increased for better UX)',
                'remaining_minute' => $user ? RateLimiter::remaining('chat:' . $user->id, 30) : 'Login required',
                'remaining_hour' => $user ? RateLimiter::remaining('chat:' . $user->id . ':hour', 300) : 'Login required',
            ]
        ],
        'current_time' => now()->timestamp,
        'user_id' => $user?->id,
        'ip_address' => $request->ip(),
        'authentication_status' => $user ? 'authenticated' : 'guest'
    ]);
});
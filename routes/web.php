<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ChatGptController;
use App\Services\CacheService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Company setup routes (accessible without company setup)
    Route::prefix('company')->name('company.')->group(function () {
        Route::get('/setup', [CompanyController::class, 'create'])->name('create');
        Route::post('/setup', [CompanyController::class, 'store'])->name('store');
        Route::get('/edit', [CompanyController::class, 'edit'])->name('edit');
        Route::put('/update', [CompanyController::class, 'update'])->name('update');
    });
    
    // Routes that require company setup
    Route::middleware('ensure.company.setup')->group(function () {
        Route::get('/dashboard', function () {
            $user = auth()->user();
            $company = $user->company;

            try {
                // Use cached data for significant performance improvement
                $stats = CacheService::getDashboardStats($company->id);
                $recentInvoices = CacheService::getRecentInvoices($company->id, 5);
                $recentQuotations = CacheService::getRecentQuotations($company->id, 5);
                $recentClients = CacheService::getRecentClients($company->id, 5);
                
                // Cache performance metrics for monitoring
                $cacheStats = CacheService::getCacheStats();
                
                return view('dashboard', compact('stats', 'recentInvoices', 'recentQuotations', 'recentClients', 'cacheStats'));
            } catch (\Exception $e) {
                \Log::error('Dashboard cache error: ' . $e->getMessage());
                
                // Fallback to direct database queries if cache fails
                $stats = [
                    'total_clients' => $company->clients()->count(),
                    'total_invoices' => $company->invoices()->count(),
                    'total_quotations' => $company->quotations()->count(),
                    'pending_invoices' => $company->invoices()->whereIn('status', ['draft', 'sent', 'viewed'])->count(),
                    'overdue_invoices' => $company->invoices()->overdue()->count(),
                    'pending_quotations' => $company->quotations()->whereIn('status', ['draft', 'sent'])->count(),
                    'total_revenue' => $company->invoices()->byStatus('paid')->sum('total'),
                    'pending_amount' => $company->invoices()->whereIn('status', ['sent', 'viewed'])->sum('total'),
                ];
                
                $recentInvoices = $company->invoices()->with(['client', 'payments'])->latest()->take(5)->get();
                $recentQuotations = $company->quotations()->with(['client'])->latest()->take(5)->get();
                $recentClients = $company->clients()->latest()->take(5)->get();
                $cacheStats = ['error' => 'Cache unavailable'];
                
                return view('dashboard', compact('stats', 'recentInvoices', 'recentQuotations', 'recentClients', 'cacheStats'));
            }
        })->name('dashboard');
        
        // Invoice and Quotation routes
        Route::resource('invoices', InvoiceController::class);
        // Invoice specific actions
        Route::patch('invoices/{invoice}/mark-sent', [InvoiceController::class, 'markAsSent'])->name('invoices.mark-sent');
        Route::patch('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
        
        // Quotation specific routes
        Route::patch('quotations/{invoice}/accept', [InvoiceController::class, 'acceptQuotation'])->name('quotations.accept');
        Route::post('quotations/{invoice}/convert', [InvoiceController::class, 'convertToInvoice'])->name('quotations.convert');
        
        // PDF routes (works for both invoices and quotations)
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'viewPdf'])->name('invoices.pdf');
        Route::get('invoices/{invoice}/download', [InvoiceController::class, 'downloadPdf'])->name('invoices.download');
        
        // Client routes
        Route::resource('clients', ClientController::class);
        
        // ChatGPT routes
        Route::get('/chatgpt', [ChatGptController::class, 'index'])->name('chatgpt.index');
        Route::post('/chatgpt/chat', [ChatGptController::class, 'chat'])->name('chatgpt.chat');
        Route::post('/chatgpt/transcribe', [ChatGptController::class, 'transcribe'])->name('chatgpt.transcribe');
        
        // Cache management routes (for admins/debugging)
        Route::prefix('cache')->name('cache.')->group(function () {
            Route::get('/stats', function () {
                $user = auth()->user();
                return response()->json([
                    'cache_stats' => CacheService::getCacheStats(),
                    'cache_health' => CacheService::healthCheck(),
                    'cache_size' => CacheService::getCacheSize(),
                    'user_company_id' => $user->company->id
                ]);
            })->name('stats');
            
            Route::post('/warm-up', function () {
                $user = auth()->user();
                CacheService::warmUpCache($user->company->id);
                return response()->json(['message' => 'Cache warmed up successfully']);
            })->name('warm-up');
            
            Route::post('/invalidate', function () {
                $user = auth()->user();
                CacheService::invalidateCompanyCache($user->company->id);
                return response()->json(['message' => 'Cache invalidated successfully']);
            })->name('invalidate');
            
            Route::post('/clear-tags', function () {
                $tags = request()->input('tags', ['dashboard', 'clients', 'invoices', 'quotations']);
                CacheService::invalidateByTags($tags);
                return response()->json(['message' => 'Cache tags cleared successfully', 'tags' => $tags]);
            })->name('clear-tags');
        });
    });
});

require __DIR__.'/auth.php';
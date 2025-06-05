<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
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
            
            // Dashboard stats
            $stats = [
                'total_clients' => $company->clients()->count(),
                'total_invoices' => $company->invoices()->count(),
                'total_quotations' => $company->quotations()->count(), // NEW
                'pending_invoices' => $company->invoices()->whereIn('status', ['draft', 'sent', 'viewed'])->count(),
                'overdue_invoices' => $company->invoices()->overdue()->count(),
                'pending_quotations' => $company->quotations()->whereIn('status', ['draft', 'sent'])->count(), // NEW
                'total_revenue' => $company->invoices()->byStatus('paid')->sum('total'),
                'pending_amount' => $company->invoices()->whereIn('status', ['sent', 'viewed'])->sum('total'),
            ];
            
            // Recent invoices
            $recentInvoices = $company->invoices()
                ->with(['client', 'payments'])
                ->latest()
                ->take(5)
                ->get();
                
            // Recent quotations (NEW)
            $recentQuotations = $company->quotations()
                ->with(['client'])
                ->latest()
                ->take(5)
                ->get();
                
            // Recent clients
            $recentClients = $company->clients()
                ->latest()
                ->take(5)
                ->get();
            
            return view('dashboard', compact('stats', 'recentInvoices', 'recentQuotations', 'recentClients'));
        })->name('dashboard');
        
        // Invoice and Quotation routes
        Route::resource('invoices', InvoiceController::class);
        // Invoice specific actions
        Route::patch('invoices/{invoice}/mark-sent', [InvoiceController::class, 'markAsSent'])->name('invoices.mark-sent');
        Route::patch('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
        
        // Quotation specific routes (NEW)
        Route::patch('quotations/{invoice}/accept', [InvoiceController::class, 'acceptQuotation'])->name('quotations.accept');
        Route::post('quotations/{invoice}/convert', [InvoiceController::class, 'convertToInvoice'])->name('quotations.convert');
        
        // PDF routes (works for both invoices and quotations)
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'viewPdf'])->name('invoices.pdf');
        Route::get('invoices/{invoice}/download', [InvoiceController::class, 'downloadPdf'])->name('invoices.download');
        
        // Client routes
        Route::resource('clients', ClientController::class);
    });
});

require __DIR__.'/auth.php';
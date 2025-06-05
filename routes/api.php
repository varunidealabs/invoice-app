<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientController;

// API Authentication
Route::post('/auth/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();
    
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    if (!$user->hasCompany()) {
        return response()->json([
            'message' => 'Company setup required',
            'error_code' => 'COMPANY_SETUP_REQUIRED'
        ], 422);
    }

    $token = $user->createToken('api-token')->plainTextToken;
    
    return response()->json([
        'token' => $token,
        'user' => $user->load('company')
    ]);
});

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user()->load('company');
    });
    
    // Logout
    Route::post('/auth/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    });
    
    // Invoices & Quotations API
    Route::apiResource('invoices', InvoiceController::class);
    Route::apiResource('clients', ClientController::class);
    
    // Custom actions
    Route::patch('invoices/{invoice}/mark-sent', [InvoiceController::class, 'markAsSent']);
    Route::patch('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid']);
    Route::post('quotations/{invoice}/convert', [InvoiceController::class, 'convertToInvoice']);
});
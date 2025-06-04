<?php
// database/migrations/2025_06_02_183743_create_companies_table.php
// Updated existing migration

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Basic Information
            $table->string('company_name');
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('website')->nullable();
            
            // Address Information
            $table->text('address_line_1');
            $table->text('address_line_2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country')->default('India');
            
            // Business Details
            $table->string('tax_id')->nullable(); // GST/Tax ID
            $table->string('business_type')->nullable();
            $table->string('logo')->nullable(); // File path for logo
            
            // Invoice Settings
            $table->string('invoice_prefix')->default('TS'); // UPDATED: Changed from 'INV' to 'TS'
            $table->integer('next_invoice_number')->default(1);
            $table->string('quotation_prefix')->default('Q-TS'); // NEW: Quotation prefix
            $table->integer('next_quotation_number')->default(1); // NEW: Quotation numbering
            $table->string('default_payment_terms')->default('Net 30');
            $table->string('currency')->default('INR');
            
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('company_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
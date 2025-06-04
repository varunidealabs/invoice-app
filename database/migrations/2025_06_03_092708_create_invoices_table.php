<?php
// database/migrations/2025_06_03_092708_create_invoices_table.php
// Updated existing migration

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('restrict');
            
            // Invoice/Quotation Details
            $table->string('invoice_number')->unique();
            $table->boolean('is_quotation')->default(false); // NEW: Distinguish quotation from invoice
            $table->date('issue_date');
            $table->date('due_date')->nullable(); // NEW: Make nullable for quotations
            $table->date('valid_until')->nullable(); // NEW: For quotation expiry
            $table->enum('status', ['draft', 'sent', 'viewed', 'paid', 'overdue', 'cancelled', 'accepted', 'expired'])->default('draft'); // NEW: Added accepted, expired
            
            // Financial
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            
            // Terms & Notes
            $table->string('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            
            // Tracking
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'is_quotation', 'status']); // NEW: Updated index
            $table->index(['client_id', 'issue_date']);
            $table->index('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
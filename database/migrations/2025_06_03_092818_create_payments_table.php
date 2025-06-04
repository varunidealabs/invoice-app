<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->enum('method', ['cash', 'bank_transfer', 'check', 'upi', 'card', 'other'])->default('bank_transfer');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['invoice_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            
            // Basic Info
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            
            // Address
            $table->text('address_line_1')->nullable();
            $table->text('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('India');
            
            // Business
            $table->string('tax_id')->nullable();
            $table->string('contact_person')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'name']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
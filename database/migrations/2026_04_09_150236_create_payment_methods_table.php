<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // bank_transfer, stripe, cash
            $table->string('label', 100)->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_iban', 50)->nullable();
            $table->string('bank_bic', 11)->nullable();
            $table->string('stripe_connect_id', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_iban')->nullable();
            $table->string('bank_bic')->nullable();
            $table->string('default_currency')->default('EUR');
            $table->unsignedSmallInteger('default_payment_terms')->default(30);
            $table->text('default_invoice_notes')->nullable();
            $table->string('invoice_logo')->nullable();
            $table->boolean('vat_exempt')->default(false);
            $table->string('vat_exempt_reason')->nullable();
            $table->enum('vat_exempt_notice_language', ['local', 'en'])->default('local');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_company_id')->nullable()->constrained('companies')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('current_company_id');
        });
        Schema::dropIfExists('companies');
    }
};

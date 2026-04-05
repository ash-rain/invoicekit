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
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('document_type', ['invoice', 'credit_note', 'debit_note', 'proforma'])->default('invoice')->after('template');
            $table->date('tax_event_date')->nullable()->after('document_type');
            $table->string('issued_by_name')->nullable()->after('tax_event_date');
            $table->string('received_by_name')->nullable()->after('issued_by_name');
            $table->foreignId('original_invoice_id')->nullable()->constrained('invoices')->nullOnDelete()->after('received_by_name');
            $table->text('cancellation_reason')->nullable()->after('original_invoice_id');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            $table->decimal('vat_amount_bgn', 12, 2)->nullable()->after('cancelled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['original_invoice_id']);
            $table->dropColumn([
                'document_type',
                'tax_event_date',
                'issued_by_name',
                'received_by_name',
                'original_invoice_id',
                'cancellation_reason',
                'cancelled_at',
                'vat_amount_bgn',
            ]);
        });
    }
};

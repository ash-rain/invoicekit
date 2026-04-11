<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->jsonb('vat_summary')->nullable()->after('vat_amount_bgn');
            $table->date('payment_due_date')->nullable()->after('due_date');
            $table->text('correction_reason')->nullable()->after('cancellation_reason');
            $table->string('original_invoice_number')->nullable()->after('original_invoice_id');
            $table->date('original_invoice_date')->nullable()->after('original_invoice_number');
            $table->renameColumn('vat_exempt_notice', 'vat_legal_basis');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('vat_legal_basis', 'vat_exempt_notice');
            $table->dropColumn([
                'vat_summary',
                'payment_due_date',
                'correction_reason',
                'original_invoice_number',
                'original_invoice_date',
            ]);
        });
    }
};

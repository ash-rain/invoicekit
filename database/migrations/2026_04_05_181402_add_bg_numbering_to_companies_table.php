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
        Schema::table('companies', function (Blueprint $table) {
            $table->enum('invoice_numbering_format', ['standard', 'bg_sequential'])->default('standard')->after('invoice_starting_number');
            $table->unsignedBigInteger('bg_invoice_sequence_start')->default(1)->after('invoice_numbering_format');
            $table->string('issued_by_default_name')->nullable()->after('bg_invoice_sequence_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['invoice_numbering_format', 'bg_invoice_sequence_start', 'issued_by_default_name']);
        });
    }
};

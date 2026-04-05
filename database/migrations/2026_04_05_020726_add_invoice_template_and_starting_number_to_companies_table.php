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
            $table->string('invoice_template', 50)->nullable()->after('invoice_prefix');
            $table->unsignedInteger('invoice_starting_number')->nullable()->after('invoice_template');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['invoice_template', 'invoice_starting_number']);
        });
    }
};

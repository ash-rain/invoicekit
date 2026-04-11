<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('vat_rate_key', 50)->nullable()->after('vat_rate');
            $table->string('place_of_supply', 2)->nullable()->after('vat_rate_key');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['vat_rate_key', 'place_of_supply']);
        });
    }
};

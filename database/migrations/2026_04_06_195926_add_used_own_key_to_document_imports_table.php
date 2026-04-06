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
        Schema::table('document_imports', function (Blueprint $table) {
            $table->boolean('used_own_key')->default(false)->after('expense_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_imports', function (Blueprint $table) {
            $table->dropColumn('used_own_key');
        });
    }
};

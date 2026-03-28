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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('reminder_before_due_days')->default(3)->after('locale');
            $table->boolean('reminder_on_due_day')->default(true)->after('reminder_before_due_days');
            $table->json('reminder_overdue_intervals')->nullable()->after('reminder_on_due_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reminder_before_due_days', 'reminder_on_due_day', 'reminder_overdue_intervals']);
        });
    }
};

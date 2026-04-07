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
            $table->timestamp('setup_guide_dismissed_at')->nullable()->after('onboarding_completed');
            $table->json('setup_guide_dismissed_steps')->nullable()->after('setup_guide_dismissed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['setup_guide_dismissed_at', 'setup_guide_dismissed_steps']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('plan', 20)->default('free')->after('email_verified_at');
            $table->string('stripe_customer_id')->nullable()->after('plan');
            $table->boolean('onboarding_completed')->default(false)->after('stripe_customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['plan', 'stripe_customer_id', 'onboarding_completed']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing bank details from companies to payment_methods
        $companies = DB::table('companies')
            ->whereNotNull('bank_iban')
            ->where('bank_iban', '!=', '')
            ->get();

        foreach ($companies as $company) {
            DB::table('payment_methods')->insert([
                'company_id' => $company->id,
                'type' => 'bank_transfer',
                'label' => $company->bank_name ?: 'Bank Transfer',
                'is_default' => true,
                'bank_name' => $company->bank_name,
                'bank_iban' => $company->bank_iban,
                'bank_bic' => $company->bank_bic,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create stripe payment methods for users with active Stripe Connect
        $users = DB::table('users')
            ->where('stripe_connect_onboarded', true)
            ->whereNotNull('stripe_connect_id')
            ->whereNotNull('current_company_id')
            ->get();

        foreach ($users as $user) {
            $hasDefault = DB::table('payment_methods')
                ->where('company_id', $user->current_company_id)
                ->where('is_default', true)
                ->exists();

            DB::table('payment_methods')->insert([
                'company_id' => $user->current_company_id,
                'type' => 'stripe',
                'label' => 'Stripe',
                'is_default' => ! $hasDefault,
                'stripe_connect_id' => $user->stripe_connect_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Data migration — no rollback needed beyond dropping the table
    }
};

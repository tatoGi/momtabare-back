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
        Schema::table('bog_payments', function (Blueprint $table) {
            $table->string('promo_code', 50)->nullable()->after('verified_at');
            $table->decimal('discount_amount', 10, 2)->nullable()->after('promo_code');
            $table->decimal('original_amount', 10, 2)->nullable()->after('discount_amount');

            // Add index for promo code lookups
            $table->index('promo_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bog_payments', function (Blueprint $table) {
            $table->dropIndex(['promo_code']);
            $table->dropColumn(['promo_code', 'discount_amount', 'original_amount']);
        });
    }
};

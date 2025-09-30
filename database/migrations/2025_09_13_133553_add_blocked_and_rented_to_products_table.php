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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('active');
            $table->boolean('is_rented')->default(false)->after('is_blocked');
            $table->timestamp('rented_at')->nullable()->after('is_rented');
            $table->foreignId('rented_by')->nullable()->after('rented_at')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['rented_by']);
            $table->dropColumn(['is_blocked', 'is_rented', 'rented_at', 'rented_by']);
        });
    }
};

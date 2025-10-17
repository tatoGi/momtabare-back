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
            $table->boolean('is_ordered')->default(false)->after('is_rented');
            $table->timestamp('ordered_at')->nullable()->after('is_ordered');
            $table->bigInteger('ordered_by')->unsigned()->nullable()->after('ordered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_ordered', 'ordered_at', 'ordered_by']);
        });
    }
};

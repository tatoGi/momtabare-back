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
        Schema::table('product_translations', function (Blueprint $table) {
            // First add the brand column if it doesn't exist
            if (! Schema::hasColumn('product_translations', 'brand')) {
                $table->string('brand')->nullable()->after('description');
            }

            // Then add location and color
            if (! Schema::hasColumn('product_translations', 'location')) {
                $table->string('location')->nullable()->after('brand');
            }

            if (! Schema::hasColumn('product_translations', 'color')) {
                $table->string('color')->nullable()->after('location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_translations', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('product_translations', 'color')) {
                $columnsToDrop[] = 'color';
            }

            if (Schema::hasColumn('product_translations', 'location')) {
                $columnsToDrop[] = 'location';
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

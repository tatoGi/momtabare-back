<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add the JSON column to product_translations
        Schema::table('product_translations', function (Blueprint $table) {
            $table->json('local_additional')->nullable()->after('description');
        });

        // Migrate existing data from individual columns to JSON
        DB::statement("
            UPDATE product_translations
            SET local_additional = JSON_OBJECT(
                'brand', COALESCE(brand, ''),
                'color', COALESCE(color, ''),
                'style', COALESCE(style, '')
            )
            WHERE brand IS NOT NULL OR color IS NOT NULL OR style IS NOT NULL
        ");

        // Drop columns from product_translations
        Schema::table('product_translations', function (Blueprint $table) {
            $table->dropColumn(['brand', 'color', 'style']);
        });

        // Drop columns from products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['color', 'size']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore columns to products table
        Schema::table('products', function (Blueprint $table) {
            $table->string('color')->nullable()->after('location');
            $table->string('size')->nullable()->after('color');
        });

        // Restore columns to product_translations
        Schema::table('product_translations', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('description');
            $table->string('color')->nullable()->after('brand');
            $table->string('style')->nullable()->after('color');
        });

        // Migrate data back from JSON to individual columns
        DB::statement("
            UPDATE product_translations
            SET
                brand = JSON_UNQUOTE(JSON_EXTRACT(local_additional, '$.brand')),
                color = JSON_UNQUOTE(JSON_EXTRACT(local_additional, '$.color')),
                style = JSON_UNQUOTE(JSON_EXTRACT(local_additional, '$.style'))
            WHERE local_additional IS NOT NULL
        ");

        // Drop JSON column
        Schema::table('product_translations', function (Blueprint $table) {
            $table->dropColumn('local_additional');
        });
    }
};

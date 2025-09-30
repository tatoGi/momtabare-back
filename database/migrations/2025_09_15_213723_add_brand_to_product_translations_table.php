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
            if (! Schema::hasColumn('product_translations', 'brand')) {
                $table->string('brand')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_translations', function (Blueprint $table) {
            if (Schema::hasColumn('product_translations', 'brand')) {
                $table->dropColumn('brand');
            }
        });
    }
};

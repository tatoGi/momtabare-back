<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProductTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_translations', function (Blueprint $table) {
            // If 'style' column exists and 'brand' doesn't exist, rename it
            if (Schema::hasColumn('product_translations', 'style') && ! Schema::hasColumn('product_translations', 'brand')) {
                $table->renameColumn('style', 'brand');
            }
            // If 'brand' column doesn't exist, add it
            elseif (! Schema::hasColumn('product_translations', 'brand')) {
                $table->string('brand')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_translations', function (Blueprint $table) {
            // If 'brand' column exists and we're rolling back
            if (Schema::hasColumn('product_translations', 'brand')) {
                // Only try to rename back to 'style' if 'style' doesn't exist
                if (! Schema::hasColumn('product_translations', 'style')) {
                    $table->renameColumn('brand', 'style');
                } else {
                    // If 'style' already exists, just drop the 'brand' column
                    $table->dropColumn('brand');
                }
            }
        });
    }
}

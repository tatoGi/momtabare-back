<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRetailerFieldsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('retailer_id')->nullable()->after('category_id');
            $table->string('contact_person')->nullable()->after('retailer_id');
            $table->string('contact_phone')->nullable()->after('contact_person');
            $table->string('currency', 3)->default('GEL')->after('price');
            $table->string('rental_period')->nullable()->after('currency');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('active');
            $table->timestamp('approved_at')->nullable()->after('status');

            $table->foreign('retailer_id')->references('id')->on('web_users')->onDelete('cascade');
            $table->index(['retailer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['retailer_id']);
            $table->dropIndex(['retailer_id', 'status']);
            $table->dropColumn([
                'retailer_id',
                'contact_person',
                'contact_phone',
                'currency',
                'rental_period',
                'status',
                'approved_at',
            ]);
        });
    }
}

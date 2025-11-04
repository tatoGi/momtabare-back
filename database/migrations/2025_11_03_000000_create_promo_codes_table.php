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
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Unique promo code
            $table->decimal('discount_percentage', 5, 2); // Discount percentage (0-100)
            $table->text('description')->nullable(); // Description of the promo code
            $table->integer('usage_limit')->nullable(); // Limit on how many times code can be used (null = unlimited)
            $table->integer('usage_count')->default(0); // How many times the code has been used
            $table->integer('per_user_limit')->nullable(); // Limit per user (null = unlimited)
            $table->boolean('is_active')->default(true); // Is the promo code active
            $table->timestamp('valid_from')->nullable(); // Start date/time
            $table->timestamp('valid_until')->nullable(); // End date/time
            $table->json('applicable_products')->nullable(); // JSON array of product IDs this applies to (null = all products)
            $table->json('applicable_categories')->nullable(); // JSON array of category IDs this applies to (null = all categories)
            $table->decimal('minimum_order_amount')->nullable(); // Minimum order amount to apply discount
            $table->boolean('applies_to_discounted_products')->default(false); // Whether discount applies to already discounted products
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};

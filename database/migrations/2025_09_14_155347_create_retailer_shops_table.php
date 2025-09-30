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
        Schema::create('retailer_shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('web_users')->onDelete('cascade');
            $table->string('avatar')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('location')->nullable();
            $table->string('contact_person');
            $table->string('contact_phone');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create translations table for shop name and description
        Schema::create('retailer_shop_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retailer_shop_id')->constrained()->onDelete('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->text('description')->nullable();

            $table->unique(['retailer_shop_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retailer_shop_translations');
        Schema::dropIfExists('retailer_shops');
    }
};

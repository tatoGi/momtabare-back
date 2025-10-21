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
        Schema::create('web_user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('web_user_id')->constrained('web_users')->onDelete('cascade');
            $table->string('name'); // Address name (e.g., Home, Office)
            $table->string('city');
            $table->string('address'); // Detailed address
            $table->decimal('lat', 10, 7); // Latitude
            $table->decimal('lng', 10, 7); // Longitude
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('web_user_id');
            $table->index(['lat', 'lng']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_user_addresses');
    }
};

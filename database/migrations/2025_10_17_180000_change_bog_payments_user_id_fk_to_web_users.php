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
        Schema::table('bog_payments', function (Blueprint $table) {
            // Drop the existing foreign key constraint to users table
            $table->dropForeign(['user_id']);

            // Add new foreign key constraint to web_users table
            $table->foreign('user_id')
                ->references('id')
                ->on('web_users')
                ->onDelete('cascade')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bog_payments', function (Blueprint $table) {
            // Drop the web_users foreign key
            $table->dropForeign(['user_id']);

            // Restore the original foreign key to users table
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('restrict');
        });
    }
};

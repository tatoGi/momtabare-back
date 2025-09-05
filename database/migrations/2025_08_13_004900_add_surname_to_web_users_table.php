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
        Schema::table('web_users', function (Blueprint $table) {
            if (!Schema::hasColumn('web_users', 'surname')) {
                $table->string('surname')->nullable()->after('first_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_users', function (Blueprint $table) {
            if (Schema::hasColumn('web_users', 'surname')) {
                $table->dropColumn('surname');
            }
        });
    }
};

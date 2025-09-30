<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_users', function (Blueprint $table) {
            if (! Schema::hasColumn('web_users', 'avatar')) {
                $table->string('avatar')->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('web_users', function (Blueprint $table) {
            if (Schema::hasColumn('web_users', 'avatar')) {
                $table->dropColumn('avatar');
            }
        });
    }
};

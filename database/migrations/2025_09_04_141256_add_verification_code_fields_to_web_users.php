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
            if (! Schema::hasColumn('web_users', 'verification_code')) {
                $table->string('verification_code')->nullable()->after('email_verification_token');
            }
            if (! Schema::hasColumn('web_users', 'verification_expires_at')) {
                $table->timestamp('verification_expires_at')->nullable()->after('verification_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_users', function (Blueprint $table) {
            if (Schema::hasColumn('web_users', 'verification_code')) {
                $table->dropColumn('verification_code');
            }
            if (Schema::hasColumn('web_users', 'verification_expires_at')) {
                $table->dropColumn('verification_expires_at');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('privacies', function (Blueprint $table) {
            $table->id();
            $table->text('text_en');
            $table->text('text_ka');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('privacies');
    }
};

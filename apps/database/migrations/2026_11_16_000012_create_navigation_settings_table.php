<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('navigation_settings', function (Blueprint $table) {
            $table->id();
            $table->json('menu');
            $table->json('footer_links');
            $table->json('footer');
            $table->json('display');
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_settings');
    }
};

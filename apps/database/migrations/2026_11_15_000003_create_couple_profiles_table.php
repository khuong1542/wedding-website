<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couple_profiles', function (Blueprint $table) {
            $table->id();
            $table->json('details');
            $table->string('groom_photo')->nullable();
            $table->string('bride_photo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couple_profiles');
    }
};

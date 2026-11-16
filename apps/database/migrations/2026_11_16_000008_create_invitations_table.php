<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('status', 20)->default('draft')->index();
            $table->string('template', 30)->default('floral');
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->string('groom_name', 120)->nullable();
            $table->string('bride_name', 120)->nullable();
            $table->date('date')->nullable();
            $table->string('time', 5)->nullable();
            $table->string('venue')->nullable();
            $table->text('message')->nullable();
            $table->string('accent', 7)->default('#D81B60');
            $table->string('seo_title', 160)->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_builders', function (Blueprint $table) {
            $table->id();
            $table->json('draft')->nullable();
            $table->json('published')->nullable();
            $table->unsignedInteger('revision')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_builders');
    }
};

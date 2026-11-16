<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email')->nullable();
            $table->text('message');
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();
        });
        Schema::create('wish_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_visible')->default(true);
            $table->boolean('allow_submissions')->default(true);
            $table->boolean('require_approval')->default(true);
            $table->unsignedSmallInteger('display_limit')->default(10);
            $table->string('sort', 10)->default('newest');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wish_settings');
        Schema::dropIfExists('wishes');
    }
};

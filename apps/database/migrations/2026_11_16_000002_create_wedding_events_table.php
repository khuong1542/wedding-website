<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_events', function (Blueprint $table) {
            $table->id();
            $table->string('side', 10)->index();
            $table->string('title', 120);
            $table->string('type', 30);
            $table->date('date')->index();
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->string('venue');
            $table->string('map_url', 1000)->nullable();
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_events');
    }
};

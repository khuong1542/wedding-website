<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_albums', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->text('description')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
        Schema::create('gallery_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->nullable()->constrained('gallery_albums')->nullOnDelete();
            $table->string('title', 120);
            $table->text('description')->nullable();
            $table->string('type', 10)->index();
            $table->string('photo')->nullable();
            $table->string('video_url', 1000)->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_media');
        Schema::dropIfExists('gallery_albums');
    }
};

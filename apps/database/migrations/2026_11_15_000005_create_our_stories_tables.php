<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('our_stories', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('');
            $table->text('introduction')->nullable();
            $table->timestamps();
        });
        Schema::create('story_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('our_story_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('date')->nullable();
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('story_milestones');
        Schema::dropIfExists('our_stories');
    }
};

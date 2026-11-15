<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('couple_profiles', function (Blueprint $table) {
            $table->string('groom_bank_qr')->nullable();
            $table->string('bride_bank_qr')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('couple_profiles', function (Blueprint $table) {
            $table->dropColumn(['groom_bank_qr', 'bride_bank_qr']);
        });
    }
};

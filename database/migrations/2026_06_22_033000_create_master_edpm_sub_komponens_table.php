<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_edpm_sub_komponens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komponen_id')->constrained('master_edpm_komponens')->cascadeOnDelete();
            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_edpm_sub_komponens');
    }
};

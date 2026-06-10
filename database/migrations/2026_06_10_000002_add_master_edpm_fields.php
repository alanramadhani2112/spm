<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_edpm_komponens', function (Blueprint $table) {
            $table->string('kode')->nullable()->after('id');
            $table->string('name')->nullable()->after('kode');
            $table->string('nama')->nullable()->after('name');
        });

        Schema::table('master_edpm_butirs', function (Blueprint $table) {
            $table->foreignId('komponen_id')->nullable()->after('id')->constrained('master_edpm_komponens')->cascadeOnDelete();
            $table->string('kode')->nullable()->after('komponen_id');
            $table->string('name')->nullable()->after('kode');
            $table->string('nama')->nullable()->after('name');
            $table->text('deskripsi')->nullable()->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('master_edpm_butirs', function (Blueprint $table) {
            $table->dropForeign(['komponen_id']);
            $table->dropColumn(['komponen_id', 'kode', 'name', 'nama', 'deskripsi']);
        });

        Schema::table('master_edpm_komponens', function (Blueprint $table) {
            $table->dropColumn(['kode', 'name', 'nama']);
        });
    }
};

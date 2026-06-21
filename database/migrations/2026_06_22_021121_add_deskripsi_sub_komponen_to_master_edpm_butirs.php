<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_edpm_butirs', function (Blueprint $table) {
            if (!Schema::hasColumn('master_edpm_butirs', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('nama');
            }
            if (!Schema::hasColumn('master_edpm_butirs', 'sub_komponen')) {
                $table->string('sub_komponen', 20)->nullable()->after('deskripsi');
            }
            if (!Schema::hasColumn('master_edpm_butirs', 'no_sk')) {
                $table->string('no_sk', 10)->nullable()->after('sub_komponen');
            }
        });
    }

    public function down(): void
    {
        Schema::table('master_edpm_butirs', function (Blueprint $table) {
            $table->dropColumn(['deskripsi', 'sub_komponen', 'no_sk']);
        });
    }
};
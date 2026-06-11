<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akreditasi_audit_logs', function (Blueprint $table) {
            $table->dropForeign(['akreditasi_id']);
            $table->foreignId('akreditasi_id')->nullable()->change();
            $table->foreign('akreditasi_id')->references('id')->on('akreditasis')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('akreditasi_audit_logs', function (Blueprint $table) {
            $table->dropForeign(['akreditasi_id']);
            $table->foreignId('akreditasi_id')->nullable(false)->change();
            $table->foreign('akreditasi_id')->references('id')->on('akreditasis')->cascadeOnDelete();
        });
    }
};

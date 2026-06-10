<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akreditasis', function (Blueprint $table) {
            $table->dateTime('assessment_deadline')->nullable()->after('assessment_opened_at');
        });
    }

    public function down(): void
    {
        Schema::table('akreditasis', function (Blueprint $table) {
            $table->dropColumn('assessment_deadline');
        });
    }
};

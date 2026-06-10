<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asesors', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->boolean('is_ketua')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('asesors', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'is_ketua']);
        });
    }
};

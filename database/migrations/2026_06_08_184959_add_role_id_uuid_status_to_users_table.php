<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->default(4)->constrained('roles');
            $table->string('uuid')->nullable()->unique();
            $table->string('status')->default('active');
        });

        try {
            DB::table('users')->insert([
                'name' => 'Admin',
                'email' => 'admin@pesantrenmu.id',
                'password' => bcrypt('password'),
                'role_id' => 1,
                'uuid' => Str::uuid(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            //
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'uuid', 'status']);
        });
    }
};

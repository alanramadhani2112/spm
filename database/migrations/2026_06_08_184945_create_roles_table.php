<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('parameter')->unique();
            $table->timestamps();
        });

        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'Admin', 'parameter' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Asesor', 'parameter' => 'asesor', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Pesantren', 'parameter' => 'pesantren', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Super Admin', 'parameter' => 'super_admin', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

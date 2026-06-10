<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });

        $all = DB::table('permissions')->pluck('id')->toArray();
        $now = now();
        $pivots = [];

        foreach ($all as $pid) {
            $pivots[] = ['role_id' => 1, 'permission_id' => $pid, 'created_at' => $now, 'updated_at' => $now];
        }
        foreach ($all as $pid) {
            $pivots[] = ['role_id' => 4, 'permission_id' => $pid, 'created_at' => $now, 'updated_at' => $now];
        }

        $asesorPerms = ['akreditasi.review2', 'akreditasi.na1', 'akreditasi.na2', 'akreditasi.nk', 'akreditasi.jadwal', 'akreditasi.laporan_asesor', 'akreditasi.submit_visitasi'];
        foreach ($asesorPerms as $key) {
            $pid = DB::table('permissions')->where('key', $key)->value('id');
            if ($pid) {
                $pivots[] = ['role_id' => 2, 'permission_id' => $pid, 'created_at' => $now, 'updated_at' => $now];
            }
        }

        $pesantrenPerms = ['akreditasi.kartu_kendali', 'akreditasi.banding'];
        foreach ($pesantrenPerms as $key) {
            $pid = DB::table('permissions')->where('key', $key)->value('id');
            if ($pid) {
                $pivots[] = ['role_id' => 3, 'permission_id' => $pid, 'created_at' => $now, 'updated_at' => $now];
            }
        }

        DB::table('role_permission')->insert($pivots);
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permission');
    }
};

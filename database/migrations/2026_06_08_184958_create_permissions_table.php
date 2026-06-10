<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->timestamps();
        });

        $permissions = [
            ['name' => 'Akses Admin Area', 'key' => 'admin.access'],
            ['name' => 'Kelola Akreditasi', 'key' => 'akreditasi.manage'],
            ['name' => 'Verifikasi Berkas', 'key' => 'akreditasi.verifikasi'],
            ['name' => 'Validasi Akhir', 'key' => 'akreditasi.validasi'],
            ['name' => 'Terbitkan SK', 'key' => 'akreditasi.sk'],
            ['name' => 'Assign Asesor', 'key' => 'akreditasi.assign'],
            ['name' => 'Review Tahap 2', 'key' => 'akreditasi.review2'],
            ['name' => 'Input NA1', 'key' => 'akreditasi.na1'],
            ['name' => 'Input NA2', 'key' => 'akreditasi.na2'],
            ['name' => 'Input NK', 'key' => 'akreditasi.nk'],
            ['name' => 'Jadwalkan Visitasi', 'key' => 'akreditasi.jadwal'],
            ['name' => 'Upload Laporan Asesor', 'key' => 'akreditasi.laporan_asesor'],
            ['name' => 'Submit Hasil Visitasi', 'key' => 'akreditasi.submit_visitasi'],
            ['name' => 'Upload Kartu Kendali', 'key' => 'akreditasi.kartu_kendali'],
            ['name' => 'Ajukan Banding', 'key' => 'akreditasi.banding'],
            ['name' => 'Proses Banding', 'key' => 'akreditasi.proses_banding'],
            ['name' => 'Kelola Master EDPM', 'key' => 'master.edpm'],
            ['name' => 'Kelola Role Permission', 'key' => 'master.role'],
            ['name' => 'Kelola Dokumen', 'key' => 'master.dokumen'],
            ['name' => 'Kelola Kategori Dokumen', 'key' => 'master.kategori'],
            ['name' => 'Super Admin Settings', 'key' => 'superadmin.settings'],
            ['name' => 'Lihat Audit Log', 'key' => 'superadmin.audit'],
            ['name' => 'View Accounts', 'key' => 'account.view'],
        ];

        foreach ($permissions as $p) {
            $p['created_at'] = now();
            $p['updated_at'] = now();
        }

        DB::table('permissions')->insert($permissions);
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};

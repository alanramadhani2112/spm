<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'admin.access',
            'akreditasi.view_all',
            'akreditasi.review_awal',
            'akreditasi.stage1_review',
            'akreditasi.assign_asesor',
            'akreditasi.validasi_akhir',
            'akreditasi.terbitkan_sk',
            'akreditasi.view_own',
            'akreditasi.submit_pengajuan',
            'akreditasi.submit_assessment',
            'akreditasi.submit_correction',
            'akreditasi.upload_kartu_kendali',
            'akreditasi.view_hasil',
            'akreditasi.submit_banding',
            'akreditasi.proses_banding',
            'asesor.review_tahap2',
            'asesor.na1',
            'asesor.na2',
            'asesor.nk',
            'asesor.jadwal_visitasi',
            'asesor.upload_laporan',
            'asesor.submit_hasil_visitasi',
            'superadmin.access',
            'settings.update',
            'role.permissions.update',
            'user.access.update',
            'superadmin.export',
            'akreditasi.final.approve',
            'sk.publish',
            'master.manage',
            'account.view',
        ];

        foreach ($permissions as $key) {
            Permission::firstOrCreate(
                ['key' => $key],
                ['name' => $key]
            );
        }
    }
}

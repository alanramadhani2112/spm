<?php

namespace Database\Seeders;

use App\Models\Akreditasi;
use App\Models\AkreditasiAuditLog;
use App\Models\Assessment;
use App\Models\Banding;
use App\Models\Document;
use App\Models\Edpm;
use App\Models\Ipm;
use App\Models\Permission;
use App\Models\Pesantren;
use App\Models\PesantrenUnit;
use App\Models\Role;
use App\Models\SdmPesantren;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminDemoSeeder extends Seeder
{
    public function run(): void
    {
        $roles = $this->roles();
        $superAdmin = $this->user('Super Admin', 'superadmin@pesantrenmu.id', $roles['super_admin']);
        $this->user('Admin', 'admin@pesantrenmu.id', $roles['admin']);

        $ketua = $this->user('Asesor Ketua Demo', 'asesor_ketua@pesantrenmu.id', $roles['asesor']);
        $anggota = $this->user('Asesor Anggota Demo', 'asesor_anggota@pesantrenmu.id', $roles['asesor']);

        $cases = [
            ['uuid' => 'DEMO-SA-001', 'name' => 'Pesantren Demo Pengajuan', 'email' => 'demo.pengajuan@pesantrenmu.id', 'status' => Akreditasi::STATUS_INITIAL_SUBMITTED, 'days' => 1],
            ['uuid' => 'DEMO-SA-002', 'name' => 'Pesantren Demo Assessment', 'email' => 'demo.assessment@pesantrenmu.id', 'status' => Akreditasi::STATUS_ASSESSMENT_OPEN, 'days' => 3, 'deadline' => 10],
            ['uuid' => 'DEMO-SA-003', 'name' => 'Pesantren Demo Review Admin', 'email' => 'demo.review-admin@pesantrenmu.id', 'status' => Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW, 'days' => 6, 'kk' => true],
            ['uuid' => 'DEMO-SA-004', 'name' => 'Pesantren Demo Assignment', 'email' => 'demo.assignment@pesantrenmu.id', 'status' => Akreditasi::STATUS_ASSESSOR_ASSIGNMENT, 'days' => 9, 'kk' => true],
            ['uuid' => 'DEMO-SA-005', 'name' => 'Pesantren Demo Review Asesor', 'email' => 'demo.review-asesor@pesantrenmu.id', 'status' => Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW, 'days' => 12, 'kk' => true, 'assessors' => true],
            ['uuid' => 'DEMO-SA-006', 'name' => 'Pesantren Demo Visitasi', 'email' => 'demo.visitasi@pesantrenmu.id', 'status' => Akreditasi::STATUS_VISITASI_SCHEDULED, 'days' => 15, 'kk' => true, 'assessors' => true, 'visitasi' => true],
            ['uuid' => 'DEMO-SA-007', 'name' => 'Pesantren Demo Scoring', 'email' => 'demo.scoring@pesantrenmu.id', 'status' => Akreditasi::STATUS_POST_VISITASI_SCORING, 'days' => 18, 'kk' => true, 'assessors' => true, 'visitasi' => true, 'scores' => true, 'laporan' => true],
            ['uuid' => 'DEMO-SA-008', 'name' => 'Pesantren Demo Validasi', 'email' => 'demo.validasi@pesantrenmu.id', 'status' => Akreditasi::STATUS_ADMIN_FINAL_VALIDATION, 'days' => 21, 'kk' => true, 'assessors' => true, 'visitasi' => true, 'scores' => true, 'laporan' => true],
            ['uuid' => 'DEMO-SA-009', 'name' => 'Pesantren Demo SK', 'email' => 'demo.sk@pesantrenmu.id', 'status' => Akreditasi::STATUS_FINAL_APPROVED, 'days' => 24, 'kk' => true, 'scores' => true, 'laporan' => true],
            ['uuid' => 'DEMO-SA-010', 'name' => 'Pesantren Demo Banding', 'email' => 'demo.banding@pesantrenmu.id', 'status' => Akreditasi::STATUS_APPEAL_SUBMITTED, 'days' => 27, 'kk' => true, 'scores' => true, 'laporan' => true, 'banding' => true],
            ['uuid' => 'DEMO-SA-011', 'name' => 'Pesantren Demo Selesai', 'email' => 'demo.selesai@pesantrenmu.id', 'status' => Akreditasi::STATUS_COMPLETED, 'days' => 30, 'kk' => true, 'scores' => true, 'laporan' => true, 'completed' => true],
        ];

        foreach ($cases as $index => $case) {
            $pesantrenUser = $this->user($case['name'], $case['email'], $roles['pesantren']);
            $pesantren = $this->pesantren($pesantrenUser, $case, $index + 1);
            $this->dataset($pesantrenUser);

            $akreditasi = $this->akreditasi($pesantrenUser, $case, $superAdmin);

            if ($case['kk'] ?? false) {
                $this->document($akreditasi, DocumentService::TYPE_KARTU_KENDALI, $pesantrenUser);
            }

            if ($case['assessors'] ?? false) {
                $this->assessment($akreditasi, $ketua, 'ketua');
                $this->assessment($akreditasi, $anggota, 'anggota');
            }

            if ($case['laporan'] ?? false) {
                $this->document($akreditasi, DocumentService::TYPE_LAPORAN_ASESOR, $ketua);
            }

            if ($case['banding'] ?? false) {
                Banding::updateOrCreate(
                    ['akreditasi_id' => $akreditasi->id],
                    [
                        'user_id' => $pesantrenUser->id,
                        'reason' => 'Demo banding untuk uji dashboard Super Admin.',
                        'status' => 'pending',
                    ]
                );
            }

            $this->audit($akreditasi, $superAdmin, $case['status']);
        }
    }

    private function roles(): array
    {
        $roles = [
            'admin' => Role::firstOrCreate(['parameter' => 'admin'], ['name' => 'Admin']),
            'asesor' => Role::firstOrCreate(['parameter' => 'asesor'], ['name' => 'Asesor']),
            'pesantren' => Role::firstOrCreate(['parameter' => 'pesantren'], ['name' => 'Pesantren']),
            'super_admin' => Role::firstOrCreate(['parameter' => 'super_admin'], ['name' => 'SuperAdmin']),
        ];

        $permissionIds = Permission::pluck('id');
        if ($permissionIds->isNotEmpty()) {
            $roles['super_admin']->permissions()->syncWithoutDetaching($permissionIds->all());
        }

        return $roles;
    }

    private function user(string $name, string $email, Role $role): User
    {
        $user = User::firstOrNew(['email' => $email]);

        if (! $user->exists) {
            $user->password = Hash::make('password');
        }

        $user->forceFill([
            'name' => $name,
            'role_id' => $role->id,
            'uuid' => $user->uuid ?: (string) Str::uuid(),
            'status' => 'active',
        ])->save();

        return $user;
    }

    private function pesantren(User $user, array $case, int $index): Pesantren
    {
        $pesantren = Pesantren::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nama_pesantren' => $case['name'],
                'ns_pesantren' => 'NSP-DEMO-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                'alamat' => 'Jl. Demo Super Admin No. '.$index,
                'kota_kabupaten' => 'Kota Demo',
                'kecamatan' => 'Kecamatan Demo',
                'kelurahan' => 'Kelurahan Demo',
                'provinsi_kode' => '32',
                'tahun_pendirian' => (string) (1990 + $index),
                'nama_mudir' => 'Mudir '.$case['name'],
                'jenjang_pendidikan_mudir' => 'S2',
                'telp_pesantren' => '021-555-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                'hp_wa' => '08120000'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                'email_pesantren' => $user->email,
                'luas_tanah' => (1000 + ($index * 120)).' m2',
                'luas_bangunan' => (650 + ($index * 80)).' m2',
                'visi' => 'Menjadi pesantren demo unggul.',
                'misi' => 'Membina kader berkemajuan.',
                'layanan_satuan_pendidikan' => ['MTs', 'MA'],
                'dok_profil' => 'demo/dok-profil-'.$index.'.pdf',
                'dok_nsp' => 'demo/dok-nsp-'.$index.'.pdf',
                'dok_renstra' => 'demo/dok-renstra-'.$index.'.pdf',
                'dok_kurikulum' => 'demo/dok-kurikulum-'.$index.'.pdf',
                'is_locked' => $index % 3 === 0,
            ]
        );

        PesantrenUnit::updateOrCreate(
            ['pesantren_id' => $pesantren->id, 'layanan_satuan_pendidikan' => 'MTs'],
            ['jumlah_rombel' => 6 + $index]
        );
        PesantrenUnit::updateOrCreate(
            ['pesantren_id' => $pesantren->id, 'layanan_satuan_pendidikan' => 'MA'],
            ['jumlah_rombel' => 3 + $index]
        );

        return $pesantren;
    }

    private function dataset(User $user): void
    {
        Ipm::updateOrCreate(['user_id' => $user->id], ['data' => ['santri_mukim' => 120, 'santri_non_mukim' => 45, 'asrama' => 4]]);
        SdmPesantren::updateOrCreate(['user_id' => $user->id], ['data' => ['ustaz_tetap' => 18, 'ustaz_tidak_tetap' => 7, 'tenaga_kependidikan' => 9]]);
        Edpm::updateOrCreate(['user_id' => $user->id], ['data' => ['kurikulum' => 'lengkap', 'kepengasuhan' => 'baik', 'sarpras' => 'cukup']]);
    }

    private function akreditasi(User $user, array $case, User $actor): Akreditasi
    {
        $createdAt = now()->subDays($case['days']);
        $data = [
            'user_id' => $user->id,
            'status' => $case['status'],
            'correction_cycle' => in_array($case['status'], [Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW, Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW], true) ? 2 : 0,
            'status_changed_at' => $createdAt->copy()->addHours(2),
            'status_changed_by' => $actor->id,
            'assessment_deadline' => isset($case['deadline']) ? now()->addDays($case['deadline']) : null,
        ];

        if ($case['visitasi'] ?? false) {
            $data['tgl_visitasi'] = now()->addDays(3);
            $data['tgl_visitasi_akhir'] = now()->addDays(4);
            $data['catatan_visitasi'] = 'Jadwal visitasi demo untuk Super Admin.';
        }

        if ($case['scores'] ?? false) {
            $data += [
                'na1' => 88,
                'is_na1_final' => true,
                'na2' => 86,
                'is_na2_final' => true,
                'nk' => 90,
                'is_nk_final' => true,
                'nv' => 88,
                'is_nv_final' => true,
                'nilai' => 88,
                'peringkat' => 'A',
            ];
        }

        if ($case['completed'] ?? false) {
            $data += [
                'nomor_sk' => 'SK-DEMO/'.now()->format('Y').'/001',
                'masa_berlaku' => now()->subMonth(),
                'masa_berlaku_akhir' => now()->addYears(5)->subMonth(),
                'sertifikat_path' => 'demo/sertifikat-demo.pdf',
            ];
        }

        $akreditasi = Akreditasi::updateOrCreate(['uuid' => $case['uuid']], $data);
        $akreditasi->forceFill(['created_at' => $createdAt, 'updated_at' => now()])->save();

        return $akreditasi;
    }

    private function assessment(Akreditasi $akreditasi, User $asesor, string $tipe): void
    {
        Assessment::updateOrCreate(
            ['akreditasi_id' => $akreditasi->id, 'asesor_id' => $asesor->id, 'tipe' => $tipe],
            []
        );
    }

    private function document(Akreditasi $akreditasi, string $type, User $uploader): void
    {
        Document::updateOrCreate(
            ['akreditasi_id' => $akreditasi->id, 'type' => $type],
            [
                'file_path' => 'demo/'.$type.'-'.$akreditasi->uuid.'.pdf',
                'uploaded_by_user_id' => $uploader->id,
            ]
        );
    }

    private function audit(Akreditasi $akreditasi, User $actor, string $status): void
    {
        AkreditasiAuditLog::firstOrCreate(
            [
                'akreditasi_id' => $akreditasi->id,
                'action_type' => 'status_changed',
                'to_status' => $status,
            ],
            [
                'user_id' => $actor->id,
                'actor_user_id' => $actor->id,
                'from_status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
                'reason' => 'Demo data Super Admin.',
                'metadata' => [
                    'status_from' => Akreditasi::STATUS_INITIAL_SUBMITTED,
                    'status_to' => $status,
                    'seed' => 'superadmin_demo',
                ],
                'created_at' => now(),
            ]
        );
    }
}

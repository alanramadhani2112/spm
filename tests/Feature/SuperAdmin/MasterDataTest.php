<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\AkreditasiAuditLog;
use App\Models\DocumentCategory;
use App\Models\Edpm;
use App\Models\Ipm;
use App\Models\MasterEdpmButir;
use App\Models\MasterEdpmKomponen;
use App\Models\Permission;
use App\Models\Pesantren;
use App\Models\PesantrenUnit;
use App\Models\Role;
use App\Models\SdmPesantren;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create(['role_id' => 4]);
    }

    public function test_super_admin_can_open_master_data_dashboard(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.master-data.index'))
            ->assertOk()
            ->assertSee('Master Data')
            ->assertSee('Master EDPM');
    }

    public function test_non_super_admin_cannot_access_master_data(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);

        $this->actingAs($admin)
            ->get(route('superadmin.master-data.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_manage_edpm_component_and_butir(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.master-data.edpm.komponen.store'), [
                'kode' => 'KOMP_TEST',
                'nama' => 'Komponen Test',
            ])
            ->assertRedirect(route('superadmin.master-data.edpm.index'));

        $komponen = MasterEdpmKomponen::where('kode', 'KOMP_TEST')->firstOrFail();
        $this->assertDatabaseHas('akreditasi_audit_logs', [
            'action_type' => 'master_edpm_komponen_created',
            'user_id' => $this->superAdmin->id,
            'akreditasi_id' => null,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.master-data.edpm.butir.store'), [
                'komponen_id' => $komponen->id,
                'kode' => 'KT.1',
                'nama' => 'Butir Test',
                'deskripsi' => 'Deskripsi butir test',
            ])
            ->assertRedirect(route('superadmin.master-data.edpm.index'));

        $this->assertDatabaseHas('master_edpm_butirs', [
            'komponen_id' => $komponen->id,
            'kode' => 'KT.1',
            'nama' => 'Butir Test',
        ]);

        $butir = MasterEdpmButir::where('kode', 'KT.1')->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->put(route('superadmin.master-data.edpm.butir.update', $butir), [
                'komponen_id' => $komponen->id,
                'kode' => 'KT.1A',
                'nama' => 'Butir Test Update',
                'deskripsi' => 'Update',
            ])
            ->assertRedirect(route('superadmin.master-data.edpm.index'));

        $this->assertDatabaseHas('master_edpm_butirs', ['kode' => 'KT.1A', 'nama' => 'Butir Test Update']);
    }

    public function test_super_admin_can_manage_document_categories(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.master-data.document-categories.store'), [
                'name' => 'Dokumen Test',
                'code' => 'dokumen_test',
                'description' => 'Kategori uji',
                'required_for_phase' => 'assessment',
                'visible_to_roles' => ['pesantren', 'asesor'],
                'asesor_scope' => 'all',
                'is_active' => '1',
            ])
            ->assertRedirect(route('superadmin.master-data.document-categories.index'));

        $category = DocumentCategory::where('name', 'Dokumen Test')->firstOrFail();
        $this->assertSame('dokumen_test', $category->code);
        $this->assertSame(['pesantren', 'asesor'], $category->visible_to_roles);
        $this->assertSame('all', $category->asesor_scope);
        $this->assertTrue($category->is_active);

        $this->actingAs($this->superAdmin)
            ->patch(route('superadmin.master-data.document-categories.toggle', $category))
            ->assertRedirect(route('superadmin.master-data.document-categories.index'));

        $this->assertFalse($category->fresh()->is_active);
    }

    public function test_document_categories_page_explains_access_rules(): void
    {
        DocumentCategory::create([
            'name' => 'Kartu Kendali',
            'code' => 'kartu_kendali',
            'visible_to_roles' => ['pesantren'],
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.master-data.document-categories.index'))
            ->assertOk()
            ->assertSee('Atur Kategori, Template, dan Akses Dokumen')
            ->assertSee('Kartu Kendali')
            ->assertSee('Role yang dapat melihat/mengakses')
            ->assertSee('Cakupan Asesor');
    }

    public function test_roles_page_displays_permission_matrix_polish(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.master-data.roles.index'))
            ->assertOk()
            ->assertSeeText('Matriks Role & Permission')
            ->assertSeeText('Ringkasan Role')
            ->assertSeeText('Mode aman aktif')
            ->assertSeeText('Edit Permission')
            ->assertSeeText('Permission Tersedia');
    }

    public function test_users_page_displays_access_control_polish(): void
    {
        $user = User::factory()->create(['role_id' => 3, 'status' => 'active']);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.master-data.users.index'))
            ->assertOk()
            ->assertSeeText('Control Center Akses Pengguna')
            ->assertSeeText('Filter Akun')
            ->assertSeeText('Tambah / Undang Pengguna')
            ->assertSeeText('Menunggu SSO')
            ->assertSee($user->email)
            ->assertSeeText('Role & Permission');
    }

    public function test_pesantren_data_control_page_displays_readiness_and_lock_state(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3, 'name' => 'User Pesantren Ready']);
        $pesantren = Pesantren::create([
            'user_id' => $pesantrenUser->id,
            'nama_pesantren' => 'Pesantren Ready',
            'ns_pesantren' => 'NSP-READY',
            'alamat' => 'Jl. Siap',
            'layanan_satuan_pendidikan' => ['MTs'],
            'provinsi_kode' => '32',
            'tahun_pendirian' => '2001',
            'is_locked' => true,
        ]);
        PesantrenUnit::create([
            'pesantren_id' => $pesantren->id,
            'layanan_satuan_pendidikan' => 'MTs',
            'jumlah_rombel' => 6,
        ]);
        Ipm::create(['user_id' => $pesantrenUser->id, 'data' => ['santri_mukim' => 100]]);
        SdmPesantren::create(['user_id' => $pesantrenUser->id, 'data' => ['ustaz_tetap' => 12]]);
        Edpm::create(['user_id' => $pesantrenUser->id, 'data' => ['status' => 'lengkap']]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.master-data.pesantren.index'))
            ->assertOk()
            ->assertSeeText('Control Center Data Pesantren')
            ->assertSeeText('Pesantren Ready')
            ->assertSeeText('Assessment Ready')
            ->assertSeeText('Terkunci');
    }

    public function test_super_admin_can_toggle_pesantren_profile_lock_with_audit_log(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $pesantren = Pesantren::create([
            'user_id' => $pesantrenUser->id,
            'nama_pesantren' => 'Pesantren Lock',
            'is_locked' => false,
        ]);

        $this->actingAs($this->superAdmin)
            ->patch(route('superadmin.master-data.pesantren.toggle-lock', $pesantren), [
                'reason' => 'Mengunci data setelah pengajuan diproses.',
            ])
            ->assertRedirect(route('superadmin.master-data.pesantren.index'));

        $this->assertTrue($pesantren->fresh()->is_locked);

        $auditLog = AkreditasiAuditLog::where('action_type', 'pesantren_profile_lock_toggled')->firstOrFail();
        $this->assertSame($this->superAdmin->id, $auditLog->user_id);
        $this->assertSame('Mengunci data setelah pengajuan diproses.', $auditLog->reason);
        $this->assertSame($pesantren->id, $auditLog->metadata['pesantren_id']);
        $this->assertFalse($auditLog->metadata['old']['is_locked']);
        $this->assertTrue($auditLog->metadata['new']['is_locked']);
    }

    public function test_super_admin_without_user_access_permission_cannot_toggle_pesantren_lock(): void
    {
        $this->revokeSuperAdminPermission('user.access.update');

        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $pesantren = Pesantren::create([
            'user_id' => $pesantrenUser->id,
            'nama_pesantren' => 'Pesantren Guard',
            'is_locked' => false,
        ]);

        $this->actingAs($this->superAdmin)
            ->patch(route('superadmin.master-data.pesantren.toggle-lock', $pesantren), [
                'reason' => 'Menguji permission.',
            ])
            ->assertForbidden();

        $this->assertFalse($pesantren->fresh()->is_locked);
    }

    public function test_pesantren_detail_page_displays_override_form(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3, 'name' => 'User Detail Pesantren']);
        $pesantren = Pesantren::create([
            'user_id' => $pesantrenUser->id,
            'nama_pesantren' => 'Pesantren Detail',
            'ns_pesantren' => 'NSP-DETAIL',
            'alamat' => 'Jl. Detail',
            'provinsi_kode' => '32',
            'tahun_pendirian' => '2002',
            'layanan_satuan_pendidikan' => ['MTs'],
            'is_locked' => true,
        ]);
        PesantrenUnit::create([
            'pesantren_id' => $pesantren->id,
            'layanan_satuan_pendidikan' => 'MTs',
            'jumlah_rombel' => 3,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.master-data.pesantren.show', $pesantren))
            ->assertOk()
            ->assertSeeText('Detail Pesantren')
            ->assertSeeText('Pesantren Detail')
            ->assertSeeText('Override Profil Pesantren')
            ->assertSeeText('Wajib alasan audit');
    }

    public function test_super_admin_can_override_locked_pesantren_profile_with_audit_log(): void
    {
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $pesantren = Pesantren::create([
            'user_id' => $pesantrenUser->id,
            'nama_pesantren' => 'Pesantren Lama',
            'ns_pesantren' => 'NSP-LAMA',
            'alamat' => 'Jl. Lama',
            'provinsi_kode' => '32',
            'tahun_pendirian' => '2001',
            'layanan_satuan_pendidikan' => ['MTs'],
            'is_locked' => true,
        ]);
        PesantrenUnit::create([
            'pesantren_id' => $pesantren->id,
            'layanan_satuan_pendidikan' => 'MTs',
            'jumlah_rombel' => 2,
        ]);

        $this->actingAs($this->superAdmin)
            ->put(route('superadmin.master-data.pesantren.update', $pesantren), [
                'nama_pesantren' => 'Pesantren Baru',
                'ns_pesantren' => 'NSP-BARU',
                'alamat' => 'Jl. Baru',
                'provinsi_kode' => '33',
                'tahun_pendirian' => '2005',
                'kota_kabupaten' => 'Kota Baru',
                'kecamatan' => 'Kecamatan Baru',
                'kelurahan' => 'Kelurahan Baru',
                'nama_mudir' => 'Mudir Baru',
                'jenjang_pendidikan_mudir' => 'S2',
                'telp_pesantren' => '021123',
                'hp_wa' => '0812345',
                'email_pesantren' => 'pesantren.baru@example.com',
                'luas_tanah' => '2000 m2',
                'luas_bangunan' => '1500 m2',
                'visi' => 'Visi baru',
                'misi' => 'Misi baru',
                'layanan_satuan_pendidikan' => ['MTs', 'MA'],
                'units' => [
                    ['layanan_satuan_pendidikan' => 'MTs', 'jumlah_rombel' => 4],
                    ['layanan_satuan_pendidikan' => 'MA', 'jumlah_rombel' => 5],
                ],
                'reason' => 'Koreksi data berdasarkan validasi dokumen resmi.',
            ])
            ->assertRedirect(route('superadmin.master-data.pesantren.show', $pesantren));

        $pesantren->refresh();
        $this->assertSame('Pesantren Baru', $pesantren->nama_pesantren);
        $this->assertSame('33', $pesantren->provinsi_kode);
        $this->assertTrue($pesantren->is_locked);
        $this->assertSame(2, $pesantren->units()->count());
        $this->assertDatabaseHas('pesantren_units', [
            'pesantren_id' => $pesantren->id,
            'layanan_satuan_pendidikan' => 'MA',
            'jumlah_rombel' => 5,
        ]);

        $auditLog = AkreditasiAuditLog::where('action_type', 'pesantren_profile_overridden')->firstOrFail();
        $this->assertSame($this->superAdmin->id, $auditLog->user_id);
        $this->assertSame('Koreksi data berdasarkan validasi dokumen resmi.', $auditLog->reason);
        $this->assertSame($pesantren->id, $auditLog->metadata['pesantren_id']);
        $this->assertTrue($auditLog->metadata['was_locked']);
        $this->assertSame('Pesantren Lama', $auditLog->metadata['old']['profile']['nama_pesantren']);
        $this->assertSame('Pesantren Baru', $auditLog->metadata['new']['profile']['nama_pesantren']);
    }

    public function test_super_admin_without_user_access_permission_cannot_override_pesantren_profile(): void
    {
        $this->revokeSuperAdminPermission('user.access.update');

        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $pesantren = Pesantren::create([
            'user_id' => $pesantrenUser->id,
            'nama_pesantren' => 'Pesantren Guard Override',
            'ns_pesantren' => 'NSP-GUARD',
            'alamat' => 'Jl. Guard',
            'provinsi_kode' => '32',
            'tahun_pendirian' => '2001',
            'layanan_satuan_pendidikan' => ['MTs'],
            'is_locked' => true,
        ]);

        $this->actingAs($this->superAdmin)
            ->put(route('superadmin.master-data.pesantren.update', $pesantren), [
                'nama_pesantren' => 'Pesantren Tidak Boleh Berubah',
                'ns_pesantren' => 'NSP-GUARD',
                'alamat' => 'Jl. Guard',
                'provinsi_kode' => '32',
                'tahun_pendirian' => '2001',
                'layanan_satuan_pendidikan' => ['MTs'],
                'units' => [
                    ['layanan_satuan_pendidikan' => 'MTs', 'jumlah_rombel' => 1],
                ],
                'reason' => 'Menguji permission override.',
            ])
            ->assertForbidden();

        $this->assertSame('Pesantren Guard Override', $pesantren->fresh()->nama_pesantren);
    }

    public function test_super_admin_can_pre_register_user_for_muhammadiyah_sso(): void
    {
        $role = Role::where('parameter', 'asesor')->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.master-data.users.store'), [
                'name' => 'User SSO Test',
                'email' => 'user.sso@example.com',
                'm_id' => '0000 0000 0000 0001',
                'nbm' => '123456',
                'role_id' => $role->id,
                'status' => 'active',
            ])
            ->assertRedirect(route('superadmin.master-data.users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'User SSO Test',
            'email' => 'user.sso@example.com',
            'm_id' => '0000 0000 0000 0001',
            'nbm' => '123456',
            'role_id' => $role->id,
            'status' => 'active',
            'sso_id' => null,
        ]);
        $this->assertDatabaseHas('akreditasi_audit_logs', [
            'action_type' => 'user_invited',
            'user_id' => $this->superAdmin->id,
            'akreditasi_id' => null,
        ]);
    }

    public function test_super_admin_can_update_role_permissions(): void
    {
        $role = Role::where('parameter', 'pesantren')->firstOrFail();
        $permission = Permission::firstOrCreate(['key' => 'master.test'], ['name' => 'Master Test']);

        $this->actingAs($this->superAdmin)
            ->put(route('superadmin.master-data.roles.permissions.update', $role), [
                'permissions' => [$permission->id],
                'reason' => 'Menyesuaikan akses role pesantren.',
            ])
            ->assertRedirect(route('superadmin.master-data.roles.index'));

        $this->assertTrue($role->fresh()->permissions()->where('permissions.id', $permission->id)->exists());
        $this->assertDatabaseHas('akreditasi_audit_logs', [
            'action_type' => 'role_permissions_updated',
            'user_id' => $this->superAdmin->id,
            'akreditasi_id' => null,
            'reason' => 'Menyesuaikan akses role pesantren.',
        ]);
    }

    public function test_super_admin_without_role_permission_update_permission_cannot_update_role_permissions(): void
    {
        $this->revokeSuperAdminPermission('role.permissions.update');

        $role = Role::where('parameter', 'pesantren')->firstOrFail();
        $permission = Permission::firstOrCreate(['key' => 'master.test'], ['name' => 'Master Test']);

        $this->actingAs($this->superAdmin)
            ->put(route('superadmin.master-data.roles.permissions.update', $role), [
                'permissions' => [$permission->id],
                'reason' => 'Menyesuaikan akses role pesantren.',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_update_user_role_and_status(): void
    {
        $user = User::factory()->create(['role_id' => 3]);
        $adminRole = Role::where('parameter', 'admin')->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->put(route('superadmin.master-data.users.update', $user), [
                'role_id' => $adminRole->id,
                'status' => 'inactive',
                'reason' => 'Akun dipindahkan ke role admin untuk pengujian.',
            ])
            ->assertRedirect(route('superadmin.master-data.users.index'));

        $user->refresh();
        $this->assertSame($adminRole->id, $user->role_id);
        $this->assertSame('inactive', $user->status);
        $this->assertDatabaseHas('akreditasi_audit_logs', [
            'action_type' => 'user_access_updated',
            'user_id' => $this->superAdmin->id,
            'akreditasi_id' => null,
            'reason' => 'Akun dipindahkan ke role admin untuk pengujian.',
        ]);
    }

    public function test_super_admin_without_user_access_permission_cannot_update_user_role_and_status(): void
    {
        $this->revokeSuperAdminPermission('user.access.update');

        $user = User::factory()->create(['role_id' => 3]);
        $adminRole = Role::where('parameter', 'admin')->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->put(route('superadmin.master-data.users.update', $user), [
                'role_id' => $adminRole->id,
                'status' => 'inactive',
                'reason' => 'Akun dipindahkan ke role admin untuk pengujian.',
            ])
            ->assertForbidden();
    }

    public function test_user_detail_page_displays_sso_management(): void
    {
        $user = User::factory()->create(['role_id' => 3]);
        $user->forceFill([
            'sso_id' => 'sso-user-001',
            'm_id' => '0000 0000 0000 0002',
            'nbm' => '987654',
            'sso_level' => 'pusat',
            'sso_role' => 'member',
            'sso_groups' => ['lp2m'],
            'last_sso_login_at' => now(),
        ])->save();

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.master-data.users.show', $user))
            ->assertOk()
            ->assertSeeText('Detail Akun Pengguna')
            ->assertSeeText('Profil SSO Muhammadiyah ID')
            ->assertSeeText('Identitas Pre-registration SSO')
            ->assertSeeText('Reset Tautan SSO')
            ->assertSeeText('sso-user-001');
    }

    public function test_super_admin_can_update_user_sso_identity_with_audit_log(): void
    {
        $user = User::factory()->create([
            'role_id' => 3,
            'm_id' => 'old-mid',
            'nbm' => 'old-nbm',
        ]);

        $this->actingAs($this->superAdmin)
            ->patch(route('superadmin.master-data.users.sso-identity.update', $user), [
                'm_id' => 'new-mid',
                'nbm' => 'new-nbm',
                'reason' => 'Memperbaiki identitas pre-registration SSO.',
            ])
            ->assertRedirect(route('superadmin.master-data.users.show', $user));

        $user->refresh();
        $this->assertSame('new-mid', $user->m_id);
        $this->assertSame('new-nbm', $user->nbm);

        $auditLog = AkreditasiAuditLog::where('action_type', 'user_sso_identity_updated')->firstOrFail();
        $this->assertSame($this->superAdmin->id, $auditLog->user_id);
        $this->assertSame('Memperbaiki identitas pre-registration SSO.', $auditLog->reason);
        $this->assertSame($user->id, $auditLog->metadata['user_id']);
        $this->assertSame('old-mid', $auditLog->metadata['old']['m_id']);
        $this->assertSame('new-mid', $auditLog->metadata['new']['m_id']);
    }

    public function test_super_admin_can_unlink_user_sso_with_audit_log(): void
    {
        $user = User::factory()->create(['role_id' => 3]);
        $user->forceFill([
            'sso_id' => 'sso-user-002',
            'm_id' => '0000 0000 0000 0003',
            'nbm' => '112233',
            'sso_level' => 'wilayah',
            'sso_role' => 'member',
            'sso_groups' => ['asesor'],
            'last_sso_login_at' => now(),
        ])->save();

        $this->actingAs($this->superAdmin)
            ->delete(route('superadmin.master-data.users.sso-link.destroy', $user), [
                'reason' => 'Reset tautan karena akun SSO salah.',
            ])
            ->assertRedirect(route('superadmin.master-data.users.show', $user));

        $user->refresh();
        $this->assertNull($user->sso_id);
        $this->assertNull($user->sso_level);
        $this->assertNull($user->sso_role);
        $this->assertNull($user->sso_groups);
        $this->assertNull($user->last_sso_login_at);
        $this->assertSame('0000 0000 0000 0003', $user->m_id);
        $this->assertSame('112233', $user->nbm);

        $auditLog = AkreditasiAuditLog::where('action_type', 'sso_user_unlinked')->firstOrFail();
        $this->assertSame($this->superAdmin->id, $auditLog->user_id);
        $this->assertSame('Reset tautan karena akun SSO salah.', $auditLog->reason);
        $this->assertSame('sso-user-002', $auditLog->metadata['old']['sso_id']);
        $this->assertNull($auditLog->metadata['new']['sso_id']);
    }

    public function test_super_admin_without_user_access_permission_cannot_unlink_user_sso(): void
    {
        $this->revokeSuperAdminPermission('user.access.update');

        $user = User::factory()->create(['role_id' => 3]);
        $user->forceFill(['sso_id' => 'sso-guard'])->save();

        $this->actingAs($this->superAdmin)
            ->delete(route('superadmin.master-data.users.sso-link.destroy', $user), [
                'reason' => 'Menguji permission reset SSO.',
            ])
            ->assertForbidden();

        $this->assertSame('sso-guard', $user->fresh()->sso_id);
    }

    private function revokeSuperAdminPermission(string $key): void
    {
        $permission = Permission::where('key', $key)->firstOrFail();
        Role::where('parameter', 'super_admin')->firstOrFail()->permissions()->detach($permission->id);
    }
}

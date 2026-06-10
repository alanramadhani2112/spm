<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\DocumentCategory;
use App\Models\MasterEdpmButir;
use App\Models\MasterEdpmKomponen;
use App\Models\Permission;
use App\Models\Role;
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
                'description' => 'Kategori uji',
                'required_for_phase' => 'assessment',
                'is_active' => '1',
            ])
            ->assertRedirect(route('superadmin.master-data.document-categories.index'));

        $category = DocumentCategory::where('name', 'Dokumen Test')->firstOrFail();
        $this->assertTrue($category->is_active);

        $this->actingAs($this->superAdmin)
            ->patch(route('superadmin.master-data.document-categories.toggle', $category))
            ->assertRedirect(route('superadmin.master-data.document-categories.index'));

        $this->assertFalse($category->fresh()->is_active);
    }

    public function test_super_admin_can_update_role_permissions(): void
    {
        $role = Role::where('parameter', 'pesantren')->firstOrFail();
        $permission = Permission::firstOrCreate(['key' => 'master.test'], ['name' => 'Master Test']);

        $this->actingAs($this->superAdmin)
            ->put(route('superadmin.master-data.roles.permissions.update', $role), [
                'permissions' => [$permission->id],
            ])
            ->assertRedirect(route('superadmin.master-data.roles.index'));

        $this->assertTrue($role->fresh()->permissions()->where('permissions.id', $permission->id)->exists());
    }

    public function test_super_admin_can_update_user_role_and_status(): void
    {
        $user = User::factory()->create(['role_id' => 3]);
        $adminRole = Role::where('parameter', 'admin')->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->put(route('superadmin.master-data.users.update', $user), [
                'role_id' => $adminRole->id,
                'status' => 'inactive',
            ])
            ->assertRedirect(route('superadmin.master-data.users.index'));

        $user->refresh();
        $this->assertSame($adminRole->id, $user->role_id);
        $this->assertSame('inactive', $user->status);
    }
}

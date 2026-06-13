<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Akreditasi;
use App\Models\Banding;
use App\Models\FailedNotification;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SuperAdminSetting;
use App\Models\User;
use App\Support\SuperAdminSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_notification_center(): void
    {
        $this->travelTo(now()->startOfDay());

        $superAdmin = User::factory()->create(['role_id' => 4]);
        $pesantren = User::factory()->create(['role_id' => 3, 'name' => 'Pesantren Inbox']);
        $akreditasi = $this->createAkreditasi($pesantren, Akreditasi::STATUS_INITIAL_SUBMITTED, now()->subDays(6));

        SuperAdminSetting::create([
            'key' => SuperAdminSettings::REVIEW_AWAL_DEADLINE,
            'value' => 5,
        ]);

        Notification::create([
            'user_id' => $superAdmin->id,
            'type' => 'akreditasi.submitted',
            'message' => 'Pengajuan baru masuk.',
            'akreditasi_id' => $akreditasi->id,
            'is_read' => false,
        ]);

        Banding::create([
            'akreditasi_id' => $akreditasi->id,
            'user_id' => $pesantren->id,
            'reason' => 'Mohon banding.',
            'status' => 'pending',
        ]);

        $this->createAkreditasi($pesantren, Akreditasi::STATUS_FINAL_APPROVED);
        FailedNotification::create();

        $this->actingAs($superAdmin)
            ->get(route('superadmin.notifications.index'))
            ->assertOk()
            ->assertSee('Pusat Notifikasi Super Admin')
            ->assertSee('Pengajuan baru masuk.')
            ->assertSee('Pesantren Inbox')
            ->assertSee('Banding Pending')
            ->assertSee('SK Pending')
            ->assertSee('SLA Watchlist')
            ->assertSee('Failed Notifications');
    }

    public function test_super_admin_can_filter_unread_notifications(): void
    {
        $superAdmin = User::factory()->create(['role_id' => 4]);

        Notification::create([
            'user_id' => $superAdmin->id,
            'type' => 'unread',
            'message' => 'Masih belum dibaca.',
            'is_read' => false,
        ]);
        Notification::create([
            'user_id' => $superAdmin->id,
            'type' => 'read',
            'message' => 'Sudah dibaca.',
            'is_read' => true,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.notifications.index', ['filter' => 'unread']))
            ->assertOk()
            ->assertSee('Masih belum dibaca.')
            ->assertDontSee('Sudah dibaca.');
    }

    public function test_super_admin_can_mark_single_notification_as_read(): void
    {
        $superAdmin = User::factory()->create(['role_id' => 4]);
        $notification = Notification::create([
            'user_id' => $superAdmin->id,
            'type' => 'akreditasi.submitted',
            'message' => 'Pengajuan baru masuk.',
            'is_read' => false,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('superadmin.notifications.mark-read', $notification))
            ->assertRedirect();

        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_super_admin_can_mark_all_own_notifications_as_read(): void
    {
        $superAdmin = User::factory()->create(['role_id' => 4]);
        $otherSuperAdmin = User::factory()->create(['role_id' => 4]);

        Notification::create([
            'user_id' => $superAdmin->id,
            'type' => 'one',
            'message' => 'Notifikasi pertama.',
            'is_read' => false,
        ]);
        Notification::create([
            'user_id' => $superAdmin->id,
            'type' => 'two',
            'message' => 'Notifikasi kedua.',
            'is_read' => false,
        ]);
        $otherNotification = Notification::create([
            'user_id' => $otherSuperAdmin->id,
            'type' => 'other',
            'message' => 'Notifikasi user lain.',
            'is_read' => false,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('superadmin.notifications.mark-all-read'))
            ->assertRedirect();

        $this->assertSame(0, Notification::where('user_id', $superAdmin->id)->where('is_read', false)->count());
        $this->assertFalse($otherNotification->fresh()->is_read);
    }

    public function test_super_admin_without_notification_permission_is_forbidden(): void
    {
        $superAdmin = User::factory()->create(['role_id' => 4]);
        $this->revokeSuperAdminPermission('superadmin.notifications');

        $this->actingAs($superAdmin)
            ->get(route('superadmin.notifications.index'))
            ->assertForbidden();
    }

    public function test_super_admin_cannot_mark_another_users_notification_as_read(): void
    {
        $superAdmin = User::factory()->create(['role_id' => 4]);
        $otherSuperAdmin = User::factory()->create(['role_id' => 4]);
        $notification = Notification::create([
            'user_id' => $otherSuperAdmin->id,
            'type' => 'other',
            'message' => 'Notifikasi user lain.',
            'is_read' => false,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('superadmin.notifications.mark-read', $notification))
            ->assertForbidden();

        $this->assertFalse($notification->fresh()->is_read);
    }

    private function createAkreditasi(User $user, string $status, $statusChangedAt = null): Akreditasi
    {
        return Akreditasi::create([
            'user_id' => $user->id,
            'uuid' => (string) Str::uuid(),
            'status' => $status,
            'status_changed_at' => $statusChangedAt,
        ]);
    }

    private function revokeSuperAdminPermission(string $key): void
    {
        $permission = Permission::where('key', $key)->firstOrFail();
        Role::where('parameter', 'super_admin')->firstOrFail()->permissions()->detach($permission->id);
    }
}

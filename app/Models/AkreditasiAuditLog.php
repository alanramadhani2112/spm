<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AkreditasiAuditLog extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function akreditasi(): BelongsTo
    {
        return $this->belongsTo(Akreditasi::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getActionTypeLabel(string $actionType): string
    {
        return match ($actionType) {
            'status_changed' => 'Status Berubah',
            'asesor_assigned' => 'Asesor Ditugaskan',
            'asesor_reassigned' => 'Asesor Diganti',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'finalized' => 'Finalisasi',
            'banding_submitted' => 'Banding Diajukan',
            'deleted' => 'Dihapus',
            'nv_changed' => 'NV Diubah',
            'document_uploaded' => 'Dokumen Diunggah',
            'document_replaced' => 'Dokumen Diganti',
            'setting_changed' => 'Setting Diubah',
            'master_edpm_komponen_created' => 'Komponen EDPM Ditambahkan',
            'master_edpm_komponen_updated' => 'Komponen EDPM Diperbarui',
            'master_edpm_komponen_deleted' => 'Komponen EDPM Dihapus',
            'master_edpm_butir_created' => 'Butir EDPM Ditambahkan',
            'master_edpm_butir_updated' => 'Butir EDPM Diperbarui',
            'master_edpm_butir_deleted' => 'Butir EDPM Dihapus',
            'document_category_created' => 'Kategori Dokumen Ditambahkan',
            'document_category_updated' => 'Kategori Dokumen Diperbarui',
            'document_category_toggled' => 'Status Kategori Dokumen Diubah',
            'document_category_deleted' => 'Kategori Dokumen Dihapus',
            'role_permissions_updated' => 'Permission Role Diperbarui',
            'user_invited' => 'Pengguna Diundang',
            'user_access_updated' => 'Akses Pengguna Diperbarui',
            'pesantren_profile_lock_toggled' => 'Lock Data Pesantren Diubah',
            'superadmin_exported' => 'Export Super Admin',
            'sso_user_linked' => 'User SSO Ditautkan',
            'sso_login_failed' => 'Login SSO Gagal',
            default => $actionType,
        };
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        throw new \RuntimeException('Audit logs are immutable');
    }

    public function delete(): ?bool
    {
        throw new \RuntimeException('Audit logs cannot be deleted');
    }
}

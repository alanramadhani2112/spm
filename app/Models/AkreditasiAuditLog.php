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

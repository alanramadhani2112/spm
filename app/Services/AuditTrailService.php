<?php

namespace App\Services;

use App\Models\AkreditasiAuditLog;

class AuditTrailService
{
    public function log(string $actionType, ?int $akreditasiId, int $userId, array $metadata = [], ?string $reason = null): void
    {
        AkreditasiAuditLog::create([
            'action_type' => $actionType,
            'akreditasi_id' => $akreditasiId ?: null,
            'user_id' => $userId,
            'actor_user_id' => $userId,
            'reason' => $reason,
            'metadata' => $metadata,
        ]);
    }

    public function logTransition(string $from, string $to, int $akreditasiId, int $userId): void
    {
        $this->log('status_changed', $akreditasiId, $userId, [
            'status_from' => $from,
            'status_to' => $to,
        ]);
    }

    public function logAssignment(int $akreditasiId, int $userId, int $oldAsesorId, int $newAsesorId): void
    {
        $actionType = $oldAsesorId === 0 ? 'asesor_assigned' : 'asesor_reassigned';

        $this->log($actionType, $akreditasiId, $userId, [
            'old_asesor_id' => $oldAsesorId,
            'new_asesor_id' => $newAsesorId,
        ]);
    }
}

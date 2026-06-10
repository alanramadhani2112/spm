<?php

namespace App\Services;

use App\Models\AkreditasiRejection;
use App\Models\SuperAdminSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RejectionService
{
    public function __construct(
        private AuditTrailService $auditTrail,
    ) {}

    public function createRejection(int $akreditasiId, int $actorUserId, array $data): AkreditasiRejection
    {
        $deadlineDays = $this->getPerbaikanDeadlineDays();

        $rejection = AkreditasiRejection::create([
            'akreditasi_id' => $akreditasiId,
            'created_by' => $actorUserId,
            'type' => $data['type'],
            'stage' => $data['stage'],
            'sections' => $data['sections'],
            'reason' => $data['reason'],
            'cycle' => $data['cycle'],
            'perbaikan_deadline' => now()->addDays($deadlineDays),
        ]);

        $this->auditTrail->log('rejection_created', $akreditasiId, $actorUserId, [
            'rejection_id' => $rejection->id,
            'type' => $data['type'],
            'stage' => $data['stage'],
            'sections' => $data['sections'],
            'cycle' => $data['cycle'],
        ]);

        return $rejection;
    }

    public function submitPerbaikan(int $akreditasiId, int $userId, array $perbaikanData): void
    {
        $rejection = $this->getActiveRejection($akreditasiId);

        if ($rejection) {
            $rejection->update([
                'resolved_at' => now(),
                'corrected_data' => $perbaikanData,
            ]);
        }

        $oldData = $rejection
            ? $rejection->only(['type', 'stage', 'sections', 'reason', 'cycle'])
            : [];

        $this->auditTrail->log('perbaikan_submitted', $akreditasiId, $userId, [
            'rejection_id' => $rejection?->id,
            'old_rejection' => $oldData,
            'corrected_data' => $perbaikanData,
        ]);
    }

    public function getActiveRejection(int $akreditasiId): ?AkreditasiRejection
    {
        return AkreditasiRejection::where('akreditasi_id', $akreditasiId)
            ->whereNull('resolved_at')
            ->latest()
            ->first();
    }

    public function getRejectionHistory(int $akreditasiId): Collection
    {
        return AkreditasiRejection::where('akreditasi_id', $akreditasiId)
            ->orderByDesc('created_at')
            ->get();
    }

    private function getPerbaikanDeadlineDays(): int
    {
        return Cache::remember('deadline_perbaikan', 3600, function () {
            $setting = SuperAdminSetting::where('key', 'deadline_perbaikan')->first();

            if ($setting && $setting->value !== null) {
                return (int) $setting->value;
            }

            return 7;
        });
    }
}

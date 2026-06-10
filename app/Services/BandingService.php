<?php

namespace App\Services;

use App\Exceptions\DeadlineExceededException;
use App\Exceptions\WorkflowException;
use App\Models\Akreditasi;
use App\Models\Banding;
use App\Models\SuperAdminSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class BandingService
{
    public function __construct(
        private AkreditasiStateMachine $stateMachine,
        private AuditTrailService $auditTrail,
    ) {}

    public function createBanding(int $akreditasiId, int $userId, string $reason): Banding
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($akreditasi->status !== Akreditasi::STATUS_FINAL_REJECTED) {
            throw new WorkflowException(
                'Banding hanya dapat diajukan untuk akreditasi dengan status final ditolak.'
            );
        }

        $existingPending = Banding::where('akreditasi_id', $akreditasiId)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            throw new WorkflowException(
                'Banding sudah pernah diajukan dan masih menunggu proses.'
            );
        }

        $this->checkBandingDeadline($akreditasi);

        $user = User::findOrFail($userId);

        $this->stateMachine->transition(
            $akreditasi,
            Akreditasi::STATUS_APPEAL_SUBMITTED,
            $user,
            $reason
        );

        $banding = Banding::create([
            'akreditasi_id' => $akreditasiId,
            'user_id' => $userId,
            'reason' => $reason,
            'status' => 'pending',
        ]);

        $this->auditTrail->log('banding_submitted', $akreditasiId, $userId, [
            'banding_id' => $banding->id,
        ]);

        return $banding;
    }

    public function processBanding(int $bandingId, int $adminUserId, string $action, ?string $response = null): Banding
    {
        $banding = Banding::findOrFail($bandingId);
        $akreditasi = Akreditasi::findOrFail($banding->akreditasi_id);
        $admin = User::findOrFail($adminUserId);

        if ($action === 'accept') {
            $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
                $admin,
                $response ?? 'Banding diterima',
                ['banding_id' => $banding->id]
            );

            $banding->status = 'accepted';
        } elseif ($action === 'reject') {
            $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_FINAL_REJECTED,
                $admin,
                $response ?? 'Banding ditolak',
                ['banding_id' => $banding->id]
            );

            $banding->status = 'rejected';
        } else {
            throw new WorkflowException("Aksi tidak valid: {$action}. Gunakan 'accept' atau 'reject'.");
        }

        $banding->admin_response = $response;
        $banding->processed_by = $adminUserId;
        $banding->processed_at = now();
        $banding->save();

        $this->auditTrail->log('banding_processed', $akreditasi->id, $adminUserId, [
            'banding_id' => $banding->id,
            'action' => $action,
            'response' => $response,
        ]);

        return $banding;
    }

    public function getActiveBanding(int $akreditasiId): ?Banding
    {
        return Banding::where('akreditasi_id', $akreditasiId)
            ->where('status', 'pending')
            ->first();
    }

    private function checkBandingDeadline(Akreditasi $akreditasi): void
    {
        $deadlineDays = $this->getBandingDeadlineDays();

        if (! $akreditasi->status_changed_at) {
            return;
        }

        $deadline = $akreditasi->status_changed_at->copy()->addDays($deadlineDays);

        if (now()->greaterThan($deadline)) {
            throw new DeadlineExceededException(
                'Batas waktu pengajuan banding telah terlampaui.'
            );
        }
    }

    private function getBandingDeadlineDays(): int
    {
        return Cache::remember('deadline_banding', 3600, function () {
            $setting = SuperAdminSetting::where('key', 'banding_deadline_days')->first();

            if ($setting && $setting->value !== null) {
                return (int) $setting->value;
            }

            return 7;
        });
    }
}

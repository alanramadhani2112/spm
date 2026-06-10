<?php

namespace App\Services;

use App\Exceptions\WorkflowException;
use App\Models\Akreditasi;
use App\Models\AkreditasiEdpm;
use App\Models\AkreditasiRejection;
use App\Models\Assessment;
use App\Models\Document;
use App\Models\Edpm;
use App\Models\Ipm;
use App\Models\Pesantren;
use App\Models\SdmPesantren;
use App\Models\SuperAdminSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AkreditasiWorkflowService
{
    public function __construct(
        private readonly AkreditasiStateMachine $stateMachine,
        private readonly PesantrenService $pesantrenService,
        private readonly DeadlineService $deadlineService,
        private readonly AuditTrailService $auditTrailService,
        private readonly NotificationService $notificationService,
        private readonly ScoringService $scoringService,
    ) {}

    public function submitPengajuanAwal(int $userId): Akreditasi
    {
        $activeAkreditasi = Akreditasi::where('user_id', $userId)
            ->whereNotIn('status', Akreditasi::TERMINAL_STATUSES)
            ->latest()
            ->first();

        if ($activeAkreditasi && $activeAkreditasi->status !== Akreditasi::STATUS_DRAFT_PROFILE) {
            throw new WorkflowException(
                'Masih ada pengajuan akreditasi aktif yang belum selesai.'
            );
        }

        $akreditasi = $activeAkreditasi ?: Akreditasi::create([
            'user_id' => $userId,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_DRAFT_PROFILE,
        ]);

        return $this->submitPengajuan($userId, $akreditasi->id);
    }

    public function submitPengajuan(int $userId, int $akreditasiId): Akreditasi
    {
        $completeness = $this->pesantrenService->checkDataCompleteness($userId);

        if (! $completeness['assessmentReady']) {
            throw new WorkflowException(
                'Data belum lengkap. Pastikan profil pesantren dan data IPM, SDM, EDPM telah diisi.'
            );
        }

        $akreditasi = Akreditasi::where('id', $akreditasiId)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($akreditasi->status !== Akreditasi::STATUS_DRAFT_PROFILE) {
            throw new WorkflowException(
                'Pengajuan hanya dapat dilakukan dari status draft profil.'
            );
        }

        $user = User::findOrFail($userId);

        $akreditasi = $this->stateMachine->transition(
            $akreditasi,
            Akreditasi::STATUS_INITIAL_SUBMITTED,
            $user,
            'Pesantren mengajukan akreditasi'
        );

        $this->auditTrailService->logTransition(
            Akreditasi::STATUS_DRAFT_PROFILE,
            Akreditasi::STATUS_INITIAL_SUBMITTED,
            $akreditasi->id,
            $userId
        );

        $pesantren = Pesantren::where('user_id', $userId)->first();
        if ($pesantren) {
            $this->pesantrenService->lockProfile($pesantren->id);
        }

        return $akreditasi;
    }

    public function adminReviewAwal(int $akreditasiId, int $adminUserId, string $action, ?string $reason = null): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $admin = User::findOrFail($adminUserId);

        if ($akreditasi->status !== Akreditasi::STATUS_INITIAL_SUBMITTED) {
            throw new WorkflowException(
                'Review awal hanya dapat dilakukan pada pengajuan yang sudah disubmit.'
            );
        }

        $validActions = ['accept', 'reject'];
        if (! in_array($action, $validActions, true)) {
            throw new WorkflowException("Aksi tidak valid: {$action}. Gunakan 'accept' atau 'reject'.");
        }

        if ($action === 'accept') {
            $akreditasi = $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_ASSESSMENT_OPEN,
                $admin,
                $reason ?? 'Admin menyetujui pengajuan awal'
            );

            $akreditasi->assessment_opened_at = now();
            $akreditasi->save();

            $this->auditTrailService->logTransition(
                Akreditasi::STATUS_INITIAL_SUBMITTED,
                Akreditasi::STATUS_ASSESSMENT_OPEN,
                $akreditasi->id,
                $adminUserId
            );

            return $akreditasi;
        }

        $akreditasi = $this->stateMachine->transition(
            $akreditasi,
            Akreditasi::STATUS_INITIAL_REJECTED,
            $admin,
            $reason ?? 'Admin menolak pengajuan awal'
        );

        $rejection = new AkreditasiRejection;
        $rejection->forceFill([
            'akreditasi_id' => $akreditasi->id,
            'type' => 'initial',
            'reason' => $reason,
            'rejected_by' => $adminUserId,
        ])->save();

        $this->auditTrailService->logTransition(
            Akreditasi::STATUS_INITIAL_SUBMITTED,
            Akreditasi::STATUS_INITIAL_REJECTED,
            $akreditasi->id,
            $adminUserId
        );

        return $akreditasi;
    }

    public function pesantrenResubmitAwal(int $akreditasiId): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($akreditasi->status !== Akreditasi::STATUS_INITIAL_REJECTED) {
            throw new WorkflowException(
                'Resubmit hanya dapat dilakukan pada pengajuan yang ditolak di tahap awal.'
            );
        }

        $user = User::findOrFail($akreditasi->user_id);

        $akreditasi = $this->stateMachine->transition(
            $akreditasi,
            Akreditasi::STATUS_INITIAL_SUBMITTED,
            $user,
            'Pesantren mengirim ulang pengajuan awal'
        );

        $this->auditTrailService->logTransition(
            Akreditasi::STATUS_INITIAL_REJECTED,
            Akreditasi::STATUS_INITIAL_SUBMITTED,
            $akreditasi->id,
            $user->id
        );

        return $akreditasi;
    }

    public function pesantrenSubmitAssessment(int $akreditasiId, array $data): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($akreditasi->status !== Akreditasi::STATUS_ASSESSMENT_OPEN) {
            throw new WorkflowException(
                'Pengumpulan asesmen hanya dapat dilakukan saat status asesmen terbuka.'
            );
        }

        $userId = $akreditasi->user_id;
        $completeness = $this->pesantrenService->checkDataCompleteness($userId);

        if (! $completeness['assessmentReady']) {
            throw new WorkflowException(
                'Data IPM, SDM, dan EDPM belum lengkap. Lengkapi semua data sebelum mengumpulkan asesmen.'
            );
        }

        $user = User::findOrFail($userId);

        DB::transaction(function () use ($akreditasi, $user, $data, $userId) {
            if (isset($data['ipm'])) {
                $this->storeAssessmentPayload(Ipm::class, $userId, $data['ipm']);
            }

            if (isset($data['sdm'])) {
                $this->storeAssessmentPayload(SdmPesantren::class, $userId, $data['sdm']);
            }

            if (isset($data['edpm'])) {
                $this->storeAssessmentPayload(Edpm::class, $userId, $data['edpm']);
            }

            $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
                $user,
                'Pesantren mengumpulkan data asesmen'
            );
        });

        $akreditasi->refresh();
        $akreditasi->assessment_submitted_at = now();
        $akreditasi->save();

        $this->auditTrailService->logTransition(
            Akreditasi::STATUS_ASSESSMENT_OPEN,
            Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
            $akreditasi->id,
            $userId
        );

        return $akreditasi;
    }

    public function adminStage1Review(int $akreditasiId, int $adminUserId, string $action, array $correctionSections = [], ?string $reason = null): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $admin = User::findOrFail($adminUserId);

        if ($akreditasi->status !== Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW) {
            throw new WorkflowException(
                'Review tahap 1 hanya dapat dilakukan pada status review admin tahap 1.'
            );
        }

        $validActions = ['approve', 'correction', 'reject_administrative'];
        if (! in_array($action, $validActions, true)) {
            throw new WorkflowException(
                "Aksi tidak valid: {$action}. Gunakan 'approve', 'correction', atau 'reject_administrative'."
            );
        }

        if ($action === 'approve') {
            $akreditasi = $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_ASSESSOR_ASSIGNMENT,
                $admin,
                $reason ?? 'Admin menyetujui asesmen tahap 1'
            );

            $this->auditTrailService->logTransition(
                Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
                Akreditasi::STATUS_ASSESSOR_ASSIGNMENT,
                $akreditasi->id,
                $adminUserId
            );

            return $akreditasi;
        }

        if ($action === 'correction') {
            $currentCycle = AkreditasiRejection::where('akreditasi_id', $akreditasiId)
                ->where('type', 'stage_1')
                ->where('stage', 'admin_stage_1')
                ->count();

            $nextCycle = $currentCycle + 1;

            $maxCyclesSetting = SuperAdminSetting::where('key', 'max_stage_1_correction_cycles')->first();
            $maxCycles = $maxCyclesSetting ? (int) ($maxCyclesSetting->value ?? 2) : 2;

            if ($nextCycle > $maxCycles) {
                throw new WorkflowException(
                    "Batas siklus koreksi tahap 1 telah tercapai ({$maxCycles}x). Gunakan 'approve' atau 'reject_administrative'."
                );
            }

            $akreditasi = $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION,
                $admin,
                $reason ?? 'Admin meminta koreksi tahap 1'
            );

            $rejection = new AkreditasiRejection;
            $rejection->forceFill([
                'akreditasi_id' => $akreditasi->id,
                'type' => 'stage_1',
                'stage' => 'admin_stage_1',
                'reason' => $reason,
                'sections' => $correctionSections,
                'cycle' => $nextCycle,
                'rejected_by' => $adminUserId,
            ])->save();

            $this->auditTrailService->logTransition(
                Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
                Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION,
                $akreditasi->id,
                $adminUserId
            );

            return $akreditasi;
        }

        $akreditasi = $this->stateMachine->transition(
            $akreditasi,
            Akreditasi::STATUS_ADMINISTRATIVE_REJECTED,
            $admin,
            $reason ?? 'Admin menolak secara administratif'
        );

        $rejection = new AkreditasiRejection;
        $rejection->forceFill([
            'akreditasi_id' => $akreditasi->id,
            'type' => 'administrative',
            'stage' => 'admin_stage_1',
            'reason' => $reason,
            'rejected_by' => $adminUserId,
        ])->save();

        $this->auditTrailService->logTransition(
            Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
            Akreditasi::STATUS_ADMINISTRATIVE_REJECTED,
            $akreditasi->id,
            $adminUserId
        );

        return $akreditasi;
    }

    public function pesantrenSubmitStage1Correction(int $akreditasiId, array $correctionData): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($akreditasi->status !== Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION) {
            throw new WorkflowException(
                'Pengiriman koreksi hanya dapat dilakukan pada status koreksi tahap 1.'
            );
        }

        $user = User::findOrFail($akreditasi->user_id);

        $latestCorrection = AkreditasiRejection::where('akreditasi_id', $akreditasiId)
            ->where('type', 'stage_1')
            ->where('stage', 'admin_stage_1')
            ->latest()
            ->first();

        $correctionSections = $latestCorrection ? ($latestCorrection->sections ?? []) : [];

        DB::transaction(function () use ($akreditasi, $user, $correctionData, $correctionSections) {
            $userId = $akreditasi->user_id;

            if (in_array('ipm', $correctionSections, true) && isset($correctionData['ipm'])) {
                $this->storeAssessmentPayload(Ipm::class, $userId, $correctionData['ipm']);
            }

            if (in_array('sdm', $correctionSections, true) && isset($correctionData['sdm'])) {
                $this->storeAssessmentPayload(SdmPesantren::class, $userId, $correctionData['sdm']);
            }

            if (in_array('edpm', $correctionSections, true) && isset($correctionData['edpm'])) {
                $this->storeAssessmentPayload(Edpm::class, $userId, $correctionData['edpm']);
            }

            $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
                $user,
                'Pesantren mengirim hasil koreksi tahap 1'
            );
        });

        $akreditasi->refresh();

        $this->auditTrailService->logTransition(
            Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION,
            Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
            $akreditasi->id,
            $user->id
        );

        return $akreditasi;
    }

    public function adminHandleStage1Limit(int $akreditasiId, int $adminUserId, string $action, ?string $reason = null): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $admin = User::findOrFail($adminUserId);

        $validStatuses = [
            Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
            Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW,
        ];

        if (! in_array($akreditasi->status, $validStatuses, true)) {
            throw new WorkflowException(
                'Penanganan batas koreksi hanya dapat dilakukan pada status review tahap 1 atau limit review.'
            );
        }

        $validActions = ['approve_by_exception', 'reject_administrative'];
        if (! in_array($action, $validActions, true)) {
            throw new WorkflowException(
                "Aksi tidak valid: {$action}. Gunakan 'approve_by_exception' atau 'reject_administrative'."
            );
        }

        if ($action === 'approve_by_exception') {
            $fromStatus = $akreditasi->status;

            $akreditasi = $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_ASSESSOR_ASSIGNMENT,
                $admin,
                $reason ?? 'Admin menyetujui dengan pengecualian (batas koreksi tercapai)'
            );

            $this->auditTrailService->logTransition(
                $fromStatus,
                Akreditasi::STATUS_ASSESSOR_ASSIGNMENT,
                $akreditasi->id,
                $adminUserId
            );

            return $akreditasi;
        }

        $fromStatus = $akreditasi->status;

        $akreditasi = $this->stateMachine->transition(
            $akreditasi,
            Akreditasi::STATUS_ADMINISTRATIVE_REJECTED,
            $admin,
            $reason ?? 'Admin menolak secara administratif (batas koreksi tercapai)'
        );

        $rejection = new AkreditasiRejection;
        $rejection->forceFill([
            'akreditasi_id' => $akreditasi->id,
            'type' => 'administrative',
            'stage' => 'admin_stage_1_limit',
            'reason' => $reason,
            'rejected_by' => $adminUserId,
        ])->save();

        $this->auditTrailService->logTransition(
            $fromStatus,
            Akreditasi::STATUS_ADMINISTRATIVE_REJECTED,
            $akreditasi->id,
            $adminUserId
        );

        return $akreditasi;
    }

    public function adminAssignAsesor(int $akreditasiId, int $ketuaId, array $anggotaIds, int $adminUserId): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($akreditasi->status !== Akreditasi::STATUS_ASSESSOR_ASSIGNMENT) {
            throw new WorkflowException(
                'Penugasan asesor hanya dapat dilakukan pada status penugasan asesor.'
            );
        }

        if (in_array($ketuaId, $anggotaIds, true)) {
            throw new WorkflowException(
                'Ketua asesor tidak boleh menjadi anggota asesor.'
            );
        }

        $allAsesorIds = array_merge([$ketuaId], $anggotaIds);
        $users = User::whereIn('id', $allAsesorIds)->get();

        foreach ($users as $user) {
            if ((int) $user->role_id !== 2) {
                throw new WorkflowException(
                    "User ID {$user->id} bukan asesor (role_id harus 2)."
                );
            }
        }

        $admin = User::findOrFail($adminUserId);

        DB::transaction(function () use ($akreditasi, $ketuaId, $anggotaIds, $admin) {
            $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
                $admin,
                'Admin menugaskan asesor'
            );

            $assessmentKetua = new Assessment;
            $assessmentKetua->forceFill([
                'akreditasi_id' => $akreditasi->id,
                'asesor_id' => $ketuaId,
                'tipe' => 'ketua',
            ])->save();

            foreach ($anggotaIds as $anggotaId) {
                $assessmentAnggota = new Assessment;
                $assessmentAnggota->forceFill([
                    'akreditasi_id' => $akreditasi->id,
                    'asesor_id' => $anggotaId,
                    'tipe' => 'anggota',
                ])->save();
            }
        });

        $akreditasi->refresh();

        $this->auditTrailService->logTransition(
            Akreditasi::STATUS_ASSESSOR_ASSIGNMENT,
            Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
            $akreditasi->id,
            $adminUserId
        );

        $this->notificationService->notifyEvent('asesor_assigned', $akreditasi->id);

        return $akreditasi;
    }

    public function ketuaAsesorStage2Review(int $akreditasiId, int $ketuaUserId, string $action, array $correctionSections = [], ?string $reason = null): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $ketua = User::findOrFail($ketuaUserId);

        if ($akreditasi->status !== Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW) {
            throw new WorkflowException(
                'Review tahap 2 hanya dapat dilakukan pada status review asesor tahap 2.'
            );
        }

        $isKetua = Assessment::where('akreditasi_id', $akreditasiId)
            ->where('asesor_id', $ketuaUserId)
            ->where('tipe', 'ketua')
            ->exists();

        if (! $isKetua) {
            throw new WorkflowException(
                'Hanya ketua asesor yang ditugaskan yang dapat melakukan review tahap 2.'
            );
        }

        $validActions = ['approve', 'correction', 'reject_administrative'];
        if (! in_array($action, $validActions, true)) {
            throw new WorkflowException(
                "Aksi tidak valid: {$action}. Gunakan 'approve', 'correction', atau 'reject_administrative'."
            );
        }

        if ($action === 'approve') {
            $akreditasi = $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_VISITASI_SCHEDULED,
                $ketua,
                $reason ?? 'Ketua asesor menyetujui review tahap 2'
            );

            $this->auditTrailService->logTransition(
                Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
                Akreditasi::STATUS_VISITASI_SCHEDULED,
                $akreditasi->id,
                $ketuaUserId
            );

            return $akreditasi;
        }

        if ($action === 'correction') {
            $currentCycle = AkreditasiRejection::where('akreditasi_id', $akreditasiId)
                ->where('type', 'stage_2')
                ->where('stage', 'assessor_stage_2')
                ->count();

            $nextCycle = $currentCycle + 1;

            $maxCyclesSetting = SuperAdminSetting::where('key', 'max_stage_2_correction_cycles')->first();
            $maxCycles = $maxCyclesSetting ? (int) ($maxCyclesSetting->value ?? 2) : 2;

            if ($nextCycle > $maxCycles) {
                throw new WorkflowException(
                    "Batas siklus koreksi tahap 2 telah tercapai ({$maxCycles}x). Gunakan 'approve' atau 'reject_administrative'."
                );
            }

            $akreditasi = $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION,
                $ketua,
                $reason ?? 'Ketua asesor meminta koreksi tahap 2'
            );

            $rejection = new AkreditasiRejection;
            $rejection->forceFill([
                'akreditasi_id' => $akreditasi->id,
                'type' => 'stage_2',
                'stage' => 'assessor_stage_2',
                'reason' => $reason,
                'sections' => $correctionSections,
                'cycle' => $nextCycle,
                'rejected_by' => $ketuaUserId,
            ])->save();

            $this->auditTrailService->logTransition(
                Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
                Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION,
                $akreditasi->id,
                $ketuaUserId
            );

            return $akreditasi;
        }

        $akreditasi = $this->stateMachine->transition(
            $akreditasi,
            Akreditasi::STATUS_ADMINISTRATIVE_REJECTED,
            $ketua,
            $reason ?? 'Ketua asesor menolak administratif tahap 2'
        );

        $this->auditTrailService->logTransition(
            Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
            Akreditasi::STATUS_ADMINISTRATIVE_REJECTED,
            $akreditasi->id,
            $ketuaUserId
        );

        return $akreditasi;
    }

    public function pesantrenSubmitStage2Correction(int $akreditasiId, array $correctionData): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($akreditasi->status !== Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION) {
            throw new WorkflowException(
                'Pengiriman koreksi tahap 2 hanya dapat dilakukan pada status koreksi tahap 2.'
            );
        }

        $user = User::findOrFail($akreditasi->user_id);

        $latestCorrection = AkreditasiRejection::where('akreditasi_id', $akreditasiId)
            ->where('type', 'stage_2')
            ->where('stage', 'assessor_stage_2')
            ->latest()
            ->first();

        $correctionSections = $latestCorrection ? ($latestCorrection->sections ?? []) : [];

        DB::transaction(function () use ($akreditasi, $user, $correctionData, $correctionSections) {
            $userId = $akreditasi->user_id;

            if (in_array('ipm', $correctionSections, true) && isset($correctionData['ipm'])) {
                $this->storeAssessmentPayload(Ipm::class, $userId, $correctionData['ipm']);
            }

            if (in_array('sdm', $correctionSections, true) && isset($correctionData['sdm'])) {
                $this->storeAssessmentPayload(SdmPesantren::class, $userId, $correctionData['sdm']);
            }

            if (in_array('edpm', $correctionSections, true) && isset($correctionData['edpm'])) {
                $this->storeAssessmentPayload(Edpm::class, $userId, $correctionData['edpm']);
            }

            $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
                $user,
                'Pesantren mengirim hasil koreksi tahap 2'
            );
        });

        $akreditasi->refresh();

        $this->auditTrailService->logTransition(
            Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION,
            Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
            $akreditasi->id,
            $user->id
        );

        return $akreditasi;
    }

    public function ketuaJadwalkanVisitasi(int $akreditasiId, int $ketuaUserId, string $tglMulai, string $tglAkhir, ?string $catatan = null): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $ketua = User::findOrFail($ketuaUserId);

        if ($akreditasi->status !== Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW) {
            throw new WorkflowException(
                'Penjadwalan visitasi hanya dapat dilakukan pada status review asesor tahap 2.'
            );
        }

        $isKetua = Assessment::where('akreditasi_id', $akreditasiId)
            ->where('asesor_id', $ketuaUserId)
            ->where('tipe', 'ketua')
            ->exists();

        if (! $isKetua) {
            throw new WorkflowException(
                'Hanya ketua asesor yang ditugaskan yang dapat menjadwalkan visitasi.'
            );
        }

        DB::transaction(function () use ($akreditasi, $ketua, $tglMulai, $tglAkhir, $catatan) {
            $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_VISITASI_SCHEDULED,
                $ketua,
                'Ketua asesor menjadwalkan visitasi'
            );

            $akreditasi->forceFill([
                'tgl_visitasi' => $tglMulai,
                'tgl_visitasi_akhir' => $tglAkhir,
                'catatan_visitasi' => $catatan,
            ])->save();
        });

        $akreditasi->refresh();

        $this->auditTrailService->logTransition(
            Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
            Akreditasi::STATUS_VISITASI_SCHEDULED,
            $akreditasi->id,
            $ketuaUserId
        );

        $this->notificationService->notifyEvent('visitasi_scheduled', $akreditasi->id);

        return $akreditasi;
    }

    public function ketuaTandaiVisitasiSelesai(int $akreditasiId, int $ketuaUserId): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $ketua = User::findOrFail($ketuaUserId);

        if ($akreditasi->status !== Akreditasi::STATUS_VISITASI_SCHEDULED) {
            throw new WorkflowException(
                'Visitasi hanya dapat ditandai selesai dari status visitasi terjadwal.'
            );
        }

        $isKetua = Assessment::where('akreditasi_id', $akreditasiId)
            ->where('asesor_id', $ketuaUserId)
            ->where('tipe', 'ketua')
            ->exists();

        if (! $isKetua) {
            throw new WorkflowException(
                'Hanya ketua asesor yang dapat menandai visitasi selesai.'
            );
        }

        DB::transaction(function () use ($akreditasi, $ketua) {
            $visitasiCompleted = $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_VISITASI_COMPLETED,
                $ketua,
                'Ketua asesor menandai visitasi selesai'
            );

            $this->stateMachine->transition(
                $visitasiCompleted,
                Akreditasi::STATUS_POST_VISITASI_SCORING,
                $ketua,
                'Otomatis masuk ke penilaian pasca visitasi'
            );
        });

        $akreditasi->refresh();

        $this->auditTrailService->logTransition(
            Akreditasi::STATUS_VISITASI_SCHEDULED,
            Akreditasi::STATUS_POST_VISITASI_SCORING,
            $akreditasi->id,
            $ketuaUserId
        );

        return $akreditasi;
    }

    public function submitNA1(int $akreditasiId, int $ketuaUserId, array $butirValues, bool $setFinal = false): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $ketua = User::findOrFail($ketuaUserId);

        if ($akreditasi->status !== Akreditasi::STATUS_POST_VISITASI_SCORING) {
            throw new WorkflowException(
                'Penilaian NA1 hanya dapat dilakukan pada status penilaian pasca visitasi.'
            );
        }

        $isKetua = Assessment::where('akreditasi_id', $akreditasiId)
            ->where('asesor_id', $ketuaUserId)
            ->where('tipe', 'ketua')
            ->exists();

        if (! $isKetua) {
            throw new WorkflowException(
                'Hanya ketua asesor yang dapat menginput NA1.'
            );
        }

        DB::transaction(function () use ($akreditasiId, $ketuaUserId, $butirValues, $setFinal) {
            foreach ($butirValues as $butirId => $value) {
                AkreditasiEdpm::updateOrCreate(
                    [
                        'akreditasi_id' => $akreditasiId,
                        'asesor_id' => $ketuaUserId,
                        'butir_id' => $butirId,
                    ],
                    [
                        'value' => $value,
                        'type' => 'na1',
                    ]
                );
            }

            if ($setFinal) {
                $akreditasi = Akreditasi::findOrFail($akreditasiId);
                $akreditasi->forceFill([
                    'na1' => $this->averageScore($butirValues),
                    'is_na1_final' => true,
                ])->save();
            }
        });

        $akreditasi->refresh();

        $this->auditTrailService->log('na1_submitted', $akreditasi->id, $ketuaUserId, [
            'butir_count' => count($butirValues),
            'set_final' => $setFinal,
        ]);

        return $akreditasi;
    }

    public function submitNA2(int $akreditasiId, int $anggotaUserId, array $butirValues, bool $setFinal = false): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $anggota = User::findOrFail($anggotaUserId);

        if ($akreditasi->status !== Akreditasi::STATUS_POST_VISITASI_SCORING) {
            throw new WorkflowException(
                'Penilaian NA2 hanya dapat dilakukan pada status penilaian pasca visitasi.'
            );
        }

        $isAnggota = Assessment::where('akreditasi_id', $akreditasiId)
            ->where('asesor_id', $anggotaUserId)
            ->where('tipe', 'anggota')
            ->exists();

        if (! $isAnggota) {
            throw new WorkflowException(
                'Hanya anggota asesor yang dapat menginput NA2.'
            );
        }

        DB::transaction(function () use ($akreditasiId, $anggotaUserId, $butirValues, $setFinal) {
            foreach ($butirValues as $butirId => $value) {
                AkreditasiEdpm::updateOrCreate(
                    [
                        'akreditasi_id' => $akreditasiId,
                        'asesor_id' => $anggotaUserId,
                        'butir_id' => $butirId,
                    ],
                    [
                        'value' => $value,
                        'type' => 'na2',
                    ]
                );
            }

            if ($setFinal) {
                $akreditasi = Akreditasi::findOrFail($akreditasiId);
                $akreditasi->forceFill([
                    'na2' => $this->averageScore($butirValues),
                    'is_na2_final' => true,
                ])->save();
            }
        });

        $akreditasi->refresh();

        $this->auditTrailService->log('na2_submitted', $akreditasi->id, $anggotaUserId, [
            'butir_count' => count($butirValues),
            'set_final' => $setFinal,
        ]);

        return $akreditasi;
    }

    public function ketuaInputNK(int $akreditasiId, int $ketuaUserId, array $nkValues, bool $setFinal = false): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $ketua = User::findOrFail($ketuaUserId);

        if (! $akreditasi->is_na1_final || ! $akreditasi->is_na2_final) {
            throw new WorkflowException(
                'Nilai NK hanya dapat diinput setelah NA1 dan NA2 ditetapkan final.'
            );
        }

        $isKetua = Assessment::where('akreditasi_id', $akreditasiId)
            ->where('asesor_id', $ketuaUserId)
            ->where('tipe', 'ketua')
            ->exists();

        if (! $isKetua) {
            throw new WorkflowException(
                'Hanya ketua asesor yang dapat menginput NK.'
            );
        }

        DB::transaction(function () use ($akreditasiId, $ketuaUserId, $nkValues, $setFinal) {
            foreach ($nkValues as $butirId => $value) {
                AkreditasiEdpm::updateOrCreate(
                    [
                        'akreditasi_id' => $akreditasiId,
                        'asesor_id' => $ketuaUserId,
                        'butir_id' => $butirId,
                    ],
                    [
                        'value' => $value,
                        'type' => 'nk',
                    ]
                );
            }

            if ($setFinal) {
                $akreditasi = Akreditasi::findOrFail($akreditasiId);
                $akreditasi->forceFill([
                    'nk' => $this->averageScore($nkValues),
                    'is_nk_final' => true,
                ])->save();
            }
        });

        $akreditasi->refresh();

        $this->auditTrailService->log('nk_submitted', $akreditasi->id, $ketuaUserId, [
            'butir_count' => count($nkValues),
            'set_final' => $setFinal,
        ]);

        return $akreditasi;
    }

    public function pesantrenUploadKartuKendali(int $akreditasiId, int $pesantrenUserId, $file): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $pesantren = User::findOrFail($pesantrenUserId);

        if ($akreditasi->user_id !== $pesantrenUserId) {
            throw new WorkflowException(
                'Hanya pesantren pemilik akreditasi yang dapat mengupload kartu kendali.'
            );
        }

        $documentService = new DocumentService;
        $documentService->upload([
            'file' => $file,
            'akreditasi_id' => $akreditasiId,
            'type' => DocumentService::TYPE_KARTU_KENDALI,
            'uploaded_by_user_id' => $pesantrenUserId,
        ]);

        $this->auditTrailService->log('kartu_kendali_uploaded', $akreditasiId, $pesantrenUserId);

        return $akreditasi;
    }

    public function ketuaUploadLaporan(int $akreditasiId, int $ketuaUserId, $individuFile, $kelompokFile): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $ketua = User::findOrFail($ketuaUserId);

        $isKetua = Assessment::where('akreditasi_id', $akreditasiId)
            ->where('asesor_id', $ketuaUserId)
            ->where('tipe', 'ketua')
            ->exists();

        if (! $isKetua) {
            throw new WorkflowException(
                'Hanya ketua asesor yang dapat mengupload laporan visitasi.'
            );
        }

        $documentService = new DocumentService;

        $documentService->upload([
            'file' => $individuFile,
            'akreditasi_id' => $akreditasiId,
            'type' => DocumentService::TYPE_LAPORAN_ASESOR,
            'uploaded_by_user_id' => $ketuaUserId,
        ]);

        $documentService->upload([
            'file' => $kelompokFile,
            'akreditasi_id' => $akreditasiId,
            'type' => DocumentService::TYPE_LAPORAN_ASESOR,
            'uploaded_by_user_id' => $ketuaUserId,
        ]);

        $this->auditTrailService->log('laporan_visitasi_uploaded', $akreditasiId, $ketuaUserId, [
            'uploaded_by' => 'ketua',
        ]);

        return $akreditasi;
    }

    public function anggotaUploadLaporanIndividu(int $akreditasiId, int $anggotaUserId, $file): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $anggota = User::findOrFail($anggotaUserId);

        $isAnggota = Assessment::where('akreditasi_id', $akreditasiId)
            ->where('asesor_id', $anggotaUserId)
            ->where('tipe', 'anggota')
            ->exists();

        if (! $isAnggota) {
            throw new WorkflowException(
                'Hanya anggota asesor yang dapat mengupload laporan individu.'
            );
        }

        $documentService = new DocumentService;
        $documentService->upload([
            'file' => $file,
            'akreditasi_id' => $akreditasiId,
            'type' => DocumentService::TYPE_LAPORAN_ASESOR,
            'uploaded_by_user_id' => $anggotaUserId,
        ]);

        $this->auditTrailService->log('laporan_individu_uploaded', $akreditasiId, $anggotaUserId, [
            'uploaded_by' => 'anggota',
        ]);

        return $akreditasi;
    }

    public function ketuaSubmitHasilVisitasi(int $akreditasiId, int $ketuaUserId): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $ketua = User::findOrFail($ketuaUserId);

        if ($akreditasi->status !== Akreditasi::STATUS_POST_VISITASI_SCORING) {
            throw new WorkflowException(
                'Submit hasil visitasi hanya dapat dilakukan pada status penilaian pasca visitasi.'
            );
        }

        $isKetua = Assessment::where('akreditasi_id', $akreditasiId)
            ->where('asesor_id', $ketuaUserId)
            ->where('tipe', 'ketua')
            ->exists();

        if (! $isKetua) {
            throw new WorkflowException(
                'Hanya ketua asesor yang dapat submit hasil visitasi.'
            );
        }

        if (! $akreditasi->is_na1_final) {
            throw new WorkflowException(
                'Nilai NA1 harus ditetapkan final sebelum submit hasil visitasi.'
            );
        }

        if (! $akreditasi->is_na2_final) {
            throw new WorkflowException(
                'Nilai NA2 harus ditetapkan final sebelum submit hasil visitasi.'
            );
        }

        if (! $akreditasi->is_nk_final) {
            throw new WorkflowException(
                'Nilai NK harus ditetapkan final sebelum submit hasil visitasi.'
            );
        }

        $laporanCount = Document::where('akreditasi_id', $akreditasiId)
            ->where('type', DocumentService::TYPE_LAPORAN_ASESOR)
            ->count();

        if ($laporanCount === 0) {
            throw new WorkflowException(
                'Laporan visitasi belum diupload. Upload laporan sebelum submit hasil visitasi.'
            );
        }

        $fromStatus = $akreditasi->status;

        $akreditasi = $this->stateMachine->transition(
            $akreditasi,
            Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED,
            $ketua,
            'Ketua asesor menyerahkan hasil visitasi'
        );

        $this->auditTrailService->logTransition(
            $fromStatus,
            Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED,
            $akreditasi->id,
            $ketuaUserId
        );

        $this->notificationService->notifyEvent('visitasi_result_submitted', $akreditasi->id);

        return $akreditasi;
    }

    public function adminValidasiAkhir(int $akreditasiId, int $adminUserId, bool $approve, ?string $reason = null, ?array $nvValues = null): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $admin = User::findOrFail($adminUserId);

        $validStatuses = [
            Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED,
            Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
        ];

        if (! in_array($akreditasi->status, $validStatuses, true)) {
            throw new WorkflowException(
                'Validasi akhir hanya dapat dilakukan pada status hasil visitasi diserahkan atau validasi akhir admin.'
            );
        }

        if (! $approve && ! $reason) {
            throw new WorkflowException(
                'Alasan penolakan wajib diisi.'
            );
        }

        if ($akreditasi->status === Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED) {
            $fromStatus = $akreditasi->status;

            $akreditasi = $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
                $admin,
                'Admin memulai validasi akhir'
            );

            $this->auditTrailService->logTransition(
                $fromStatus,
                Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
                $akreditasi->id,
                $adminUserId
            );
        }

        $nkEntries = AkreditasiEdpm::where('akreditasi_id', $akreditasi->id)
            ->where('type', 'nk')
            ->get();

        $hasOverride = false;

        foreach ($nkEntries as $nkEntry) {
            $nvValue = $nkEntry->value;

            if ($nvValues && isset($nvValues[$nkEntry->butir_id])) {
                $nvValue = (float) $nvValues[$nkEntry->butir_id];
                $hasOverride = true;
            }

            AkreditasiEdpm::updateOrCreate(
                [
                    'akreditasi_id' => $akreditasi->id,
                    'asesor_id' => $admin->id,
                    'butir_id' => $nkEntry->butir_id,
                ],
                [
                    'value' => $nvValue,
                    'type' => 'nv',
                ]
            );
        }

        if ($hasOverride && ! $reason) {
            throw new WorkflowException(
                'Alasan wajib diisi ketika nilai NV diubah (override).'
            );
        }

        $computedNv = $nkEntries->count() > 0
            ? AkreditasiEdpm::where('akreditasi_id', $akreditasi->id)
                ->where('type', 'nv')
                ->avg('value') ?? 0
            : 0;

        $akreditasi->forceFill([
            'nv' => $computedNv,
            'nilai' => $this->finalScoreFromAverage((float) $computedNv),
            'peringkat' => $this->peringkatFromFinalScore($this->finalScoreFromAverage((float) $computedNv)),
            'is_nv_final' => true,
            'nv_override' => $hasOverride,
            'nv_override_reason' => $hasOverride ? $reason : null,
        ])->save();

        if ($approve) {
            $fromStatus = $akreditasi->status;

            $akreditasi = $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_FINAL_APPROVED,
                $admin,
                $reason ?? 'Admin menyetujui validasi akhir'
            );

            $this->auditTrailService->logTransition(
                $fromStatus,
                Akreditasi::STATUS_FINAL_APPROVED,
                $akreditasi->id,
                $adminUserId
            );

            $this->notificationService->notifyEvent('final_approved', $akreditasi->id);
        } else {
            $fromStatus = $akreditasi->status;

            $akreditasi = $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_FINAL_REJECTED,
                $admin,
                $reason
            );

            $rejection = new AkreditasiRejection;
            $rejection->forceFill([
                'akreditasi_id' => $akreditasi->id,
                'type' => 'final',
                'stage' => 'admin_final_validation',
                'reason' => $reason,
                'rejected_by' => $adminUserId,
            ])->save();

            $this->auditTrailService->logTransition(
                $fromStatus,
                Akreditasi::STATUS_FINAL_REJECTED,
                $akreditasi->id,
                $adminUserId
            );

            $this->notificationService->notifyEvent('final_rejected', $akreditasi->id);
        }

        return $akreditasi;
    }

    public function adminTerbitkanSK(int $akreditasiId, int $adminUserId, string $nomorSk, string $masaBerlaku): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $admin = User::findOrFail($adminUserId);

        if ($akreditasi->status !== Akreditasi::STATUS_FINAL_APPROVED) {
            throw new WorkflowException(
                'SK hanya dapat diterbitkan pada status final disetujui.'
            );
        }

        DB::transaction(function () use ($akreditasi, $admin, $nomorSk, $masaBerlaku) {
            $akreditasi->forceFill([
                'nomor_sk' => $nomorSk,
                'masa_berlaku' => $masaBerlaku,
            ])->save();

            $this->stateMachine->transition(
                $akreditasi,
                Akreditasi::STATUS_COMPLETED,
                $admin,
                'Admin menerbitkan SK/Sertifikat'
            );
        });

        $akreditasi->refresh();

        $this->auditTrailService->logTransition(
            Akreditasi::STATUS_FINAL_APPROVED,
            Akreditasi::STATUS_COMPLETED,
            $akreditasi->id,
            $adminUserId
        );

        $this->notificationService->notifyEvent('sk_terbit', $akreditasi->id);

        return $akreditasi;
    }

    public function pesantrenSubmitBanding(int $akreditasiId, int $pesantrenUserId, string $alasan): Akreditasi
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($akreditasi->user_id !== $pesantrenUserId) {
            throw new WorkflowException(
                'Hanya pesantren pemilik akreditasi yang dapat mengajukan banding.'
            );
        }

        if ($akreditasi->status !== Akreditasi::STATUS_FINAL_REJECTED) {
            throw new WorkflowException(
                'Banding hanya dapat diajukan pada status final ditolak.'
            );
        }

        $bandingService = app(BandingService::class);
        $bandingService->createBanding($akreditasiId, $pesantrenUserId, $alasan);

        $akreditasi->refresh();

        $this->auditTrailService->logTransition(
            Akreditasi::STATUS_FINAL_REJECTED,
            Akreditasi::STATUS_APPEAL_SUBMITTED,
            $akreditasi->id,
            $pesantrenUserId
        );

        $this->notificationService->notifyEvent('appeal_submitted', $akreditasi->id);

        return $akreditasi;
    }

    private function storeAssessmentPayload(string $modelClass, int $userId, array $payload): void
    {
        $record = $modelClass::firstOrNew(['user_id' => $userId]);
        $record->forceFill([
            'user_id' => $userId,
            'data' => array_replace($record->data ?? [], $payload),
        ])->save();
    }

    private function averageScore(array $values): float
    {
        $numericValues = array_map('floatval', array_values($values));

        if ($numericValues === []) {
            return 0;
        }

        return round(array_sum($numericValues) / count($numericValues), 2);
    }

    private function finalScoreFromAverage(float $average): float
    {
        return $this->scoringService->calculateFinalScoreFromAverage($average);
    }

    private function peringkatFromFinalScore(float $score): string
    {
        return $this->scoringService->calculatePeringkat($score);
    }
}

<?php

namespace App\Services;

use App\Exceptions\InvalidTransitionException;
use App\Exceptions\StaleStateException;
use App\Models\Akreditasi;
use App\Models\AkreditasiAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AkreditasiStateMachine
{
    public const STATUS_DRAFT_PROFILE = 'draft_profile';
    public const STATUS_INITIAL_SUBMITTED = 'initial_submitted';
    public const STATUS_INITIAL_REJECTED = 'initial_rejected';
    public const STATUS_ASSESSMENT_OPEN = 'assessment_open';
    public const STATUS_ADMIN_STAGE_1_REVIEW = 'admin_stage_1_review';
    public const STATUS_ADMIN_STAGE_1_CORRECTION = 'admin_stage_1_correction';
    public const STATUS_ADMIN_STAGE_1_LIMIT_REVIEW = 'admin_stage_1_limit_review';
    public const STATUS_ASSESSOR_ASSIGNMENT = 'assessor_assignment';
    public const STATUS_ASSESSOR_STAGE_2_REVIEW = 'assessor_stage_2_review';
    public const STATUS_ASSESSOR_STAGE_2_CORRECTION = 'assessor_stage_2_correction';
    public const STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW = 'assessor_stage_2_limit_review';
    public const STATUS_VISITASI_SCHEDULED = 'visitasi_scheduled';
    public const STATUS_VISITASI_COMPLETED = 'visitasi_completed';
    public const STATUS_POST_VISITASI_SCORING = 'post_visitasi_scoring';
    public const STATUS_VISITASI_RESULT_SUBMITTED = 'visitasi_result_submitted';
    public const STATUS_ADMIN_FINAL_VALIDATION = 'admin_final_validation';
    public const STATUS_ADMINISTRATIVE_REJECTED = 'administrative_rejected';
    public const STATUS_FINAL_REJECTED = 'final_rejected';
    public const STATUS_APPEAL_SUBMITTED = 'appeal_submitted';
    public const STATUS_FINAL_APPROVED = 'final_approved';
    public const STATUS_COMPLETED = 'completed';

    public const TRANSITIONS = [
        self::STATUS_DRAFT_PROFILE => [
            self::STATUS_INITIAL_SUBMITTED,
        ],
        self::STATUS_INITIAL_SUBMITTED => [
            self::STATUS_INITIAL_REJECTED,
            self::STATUS_ASSESSMENT_OPEN,
        ],
        self::STATUS_INITIAL_REJECTED => [
            self::STATUS_INITIAL_SUBMITTED,
        ],
        self::STATUS_ASSESSMENT_OPEN => [
            self::STATUS_ADMIN_STAGE_1_REVIEW,
        ],
        self::STATUS_ADMIN_STAGE_1_REVIEW => [
            self::STATUS_ADMIN_STAGE_1_CORRECTION,
            self::STATUS_ASSESSOR_ASSIGNMENT,
            self::STATUS_ADMINISTRATIVE_REJECTED,
        ],
        self::STATUS_ADMIN_STAGE_1_CORRECTION => [
            self::STATUS_ADMIN_STAGE_1_REVIEW,
            self::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW,
        ],
        self::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW => [
            self::STATUS_ASSESSOR_ASSIGNMENT,
            self::STATUS_ADMINISTRATIVE_REJECTED,
        ],
        self::STATUS_ASSESSOR_ASSIGNMENT => [
            self::STATUS_ASSESSOR_STAGE_2_REVIEW,
        ],
        self::STATUS_ASSESSOR_STAGE_2_REVIEW => [
            self::STATUS_ASSESSOR_STAGE_2_CORRECTION,
            self::STATUS_VISITASI_SCHEDULED,
            self::STATUS_ADMINISTRATIVE_REJECTED,
        ],
        self::STATUS_ASSESSOR_STAGE_2_CORRECTION => [
            self::STATUS_ASSESSOR_STAGE_2_REVIEW,
            self::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW,
        ],
        self::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW => [
            self::STATUS_VISITASI_SCHEDULED,
            self::STATUS_ADMINISTRATIVE_REJECTED,
        ],
        self::STATUS_VISITASI_SCHEDULED => [
            self::STATUS_VISITASI_COMPLETED,
            self::STATUS_ASSESSOR_STAGE_2_REVIEW,
        ],
        self::STATUS_VISITASI_COMPLETED => [
            self::STATUS_POST_VISITASI_SCORING,
        ],
        self::STATUS_POST_VISITASI_SCORING => [
            self::STATUS_VISITASI_RESULT_SUBMITTED,
        ],
        self::STATUS_VISITASI_RESULT_SUBMITTED => [
            self::STATUS_ADMIN_FINAL_VALIDATION,
        ],
        self::STATUS_ADMIN_FINAL_VALIDATION => [
            self::STATUS_FINAL_REJECTED,
            self::STATUS_FINAL_APPROVED,
        ],
        self::STATUS_FINAL_REJECTED => [
            self::STATUS_APPEAL_SUBMITTED,
        ],
        self::STATUS_APPEAL_SUBMITTED => [
            self::STATUS_ADMIN_FINAL_VALIDATION,
            self::STATUS_FINAL_REJECTED,
        ],
        self::STATUS_FINAL_APPROVED => [
            self::STATUS_COMPLETED,
        ],
        self::STATUS_ADMINISTRATIVE_REJECTED => [
            self::STATUS_INITIAL_SUBMITTED,
            self::STATUS_FINAL_REJECTED,
            self::STATUS_APPEAL_SUBMITTED,
        ],
        self::STATUS_COMPLETED => [],
    ];

    public const TERMINAL_STATUSES = [
        self::STATUS_COMPLETED,
    ];

    public const CORRECTION_STATUSES = [
        self::STATUS_ADMIN_STAGE_1_CORRECTION,
        self::STATUS_ASSESSOR_STAGE_2_CORRECTION,
    ];

    public function transition(Akreditasi $akreditasi, string $newStatus, User $actor, ?string $reason = null, ?array $metadata = null): Akreditasi
    {
        if (!$this->canTransition($akreditasi->status, $newStatus)) {
            throw new InvalidTransitionException($akreditasi->status, $newStatus);
        }

        if ($akreditasi->status === $newStatus) {
            return $akreditasi;
        }

        return DB::transaction(function () use ($akreditasi, $newStatus, $actor, $reason, $metadata) {
            $fromStatus = $akreditasi->status;
            $originalUpdatedAt = $akreditasi->updated_at;

            $fresh = Akreditasi::where('id', $akreditasi->id)
                ->where('updated_at', $originalUpdatedAt)
                ->lockForUpdate()
                ->first();

            if (!$fresh) {
                throw new StaleStateException(
                    "Akreditasi [{$akreditasi->id}] has been modified by another process."
                );
            }

            $fresh->status = $newStatus;
            $fresh->status_changed_at = now();
            $fresh->status_changed_by = $actor->id;

            if ($reason !== null) {
                $fresh->status_reason = $reason;
            }

            $fresh->save();

            AkreditasiAuditLog::create([
                'akreditasi_id' => $akreditasi->id,
                'from_status' => $fromStatus,
                'to_status' => $newStatus,
                'actor_user_id' => $actor->id,
                'reason' => $reason,
                'metadata' => $metadata,
            ]);

            return $fresh;
        });
    }

    public function canTransition(string $fromStatus, string $toStatus): bool
    {
        return in_array($toStatus, $this->getPermittedTransitions($fromStatus), true);
    }

    public function getPermittedTransitions(string $status): array
    {
        return self::TRANSITIONS[$status] ?? [];
    }

    public function isTerminal(string $status): bool
    {
        return in_array($status, self::TERMINAL_STATUSES, true);
    }

    public function isActive(string $status): bool
    {
        return !$this->isTerminal($status);
    }

    public function isCorrectionStage(string $status): bool
    {
        return in_array($status, self::CORRECTION_STATUSES, true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Akreditasi extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT_PROFILE = 'draft_profile';
    public const STATUS_INITIAL_SUBMITTED = 'initial_submitted';
    public const STATUS_ASSESSMENT_OPEN = 'assessment_open';
    public const STATUS_INITIAL_REJECTED = 'initial_rejected';
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
    public const STATUS_FINAL_APPROVED = 'final_approved';
    public const STATUS_FINAL_REJECTED = 'final_rejected';
    public const STATUS_APPEAL_SUBMITTED = 'appeal_submitted';
    public const STATUS_COMPLETED = 'completed';

    public const ACTIVE_STATUSES = [
        self::STATUS_DRAFT_PROFILE,
        self::STATUS_INITIAL_SUBMITTED,
        self::STATUS_ASSESSMENT_OPEN,
        self::STATUS_INITIAL_REJECTED,
        self::STATUS_ADMIN_STAGE_1_REVIEW,
        self::STATUS_ADMIN_STAGE_1_CORRECTION,
        self::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW,
        self::STATUS_ASSESSOR_ASSIGNMENT,
        self::STATUS_ASSESSOR_STAGE_2_REVIEW,
        self::STATUS_ASSESSOR_STAGE_2_CORRECTION,
        self::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW,
        self::STATUS_VISITASI_SCHEDULED,
        self::STATUS_VISITASI_COMPLETED,
        self::STATUS_POST_VISITASI_SCORING,
        self::STATUS_VISITASI_RESULT_SUBMITTED,
        self::STATUS_ADMIN_FINAL_VALIDATION,
        self::STATUS_ADMINISTRATIVE_REJECTED,
        self::STATUS_APPEAL_SUBMITTED,
    ];

    public const CORRECTION_STATUSES = [
        self::STATUS_ADMIN_STAGE_1_CORRECTION,
        self::STATUS_ASSESSOR_STAGE_2_CORRECTION,
    ];

    public const TERMINAL_STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_FINAL_APPROVED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_DRAFT_PROFILE => 'Draft Profil',
        self::STATUS_INITIAL_SUBMITTED => 'Pengajuan Awal',
        self::STATUS_ASSESSMENT_OPEN => 'Asesmen Terbuka',
        self::STATUS_INITIAL_REJECTED => 'Ditolak Tahap 1',
        self::STATUS_ADMIN_STAGE_1_REVIEW => 'Review Admin Tahap 1',
        self::STATUS_ADMIN_STAGE_1_CORRECTION => 'Koreksi Tahap 1',
        self::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW => 'Review Batas Tahap 1',
        self::STATUS_ASSESSOR_ASSIGNMENT => 'Penugasan Asesor',
        self::STATUS_ASSESSOR_STAGE_2_REVIEW => 'Review Asesor Tahap 2',
        self::STATUS_ASSESSOR_STAGE_2_CORRECTION => 'Koreksi Tahap 2',
        self::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW => 'Review Batas Tahap 2',
        self::STATUS_VISITASI_SCHEDULED => 'Visitasi Terjadwal',
        self::STATUS_VISITASI_COMPLETED => 'Visitasi Selesai',
        self::STATUS_POST_VISITASI_SCORING => 'Penilaian Pasca Visitasi',
        self::STATUS_VISITASI_RESULT_SUBMITTED => 'Hasil Visitasi Diserahkan',
        self::STATUS_ADMIN_FINAL_VALIDATION => 'Validasi Akhir Admin',
        self::STATUS_ADMINISTRATIVE_REJECTED => 'Ditolak Administratif',
        self::STATUS_FINAL_APPROVED => 'Final Disetujui',
        self::STATUS_FINAL_REJECTED => 'Final Ditolak',
        self::STATUS_APPEAL_SUBMITTED => 'Banding Diajukan',
        self::STATUS_COMPLETED => 'Selesai',
    ];

    public const PESANTREN_FILLABLE = [
        'user_id',
        'uuid',
        'catatan',
        'kartu_kendali',
    ];

    protected $fillable = [
        'user_id',
        'uuid',
        'status',
        'nomor_sk',
        'catatan',
        'tgl_visitasi',
        'tgl_visitasi_akhir',
        'na1',
        'is_na1_final',
        'na2',
        'is_na2_final',
        'nk',
        'is_nk_final',
        'nv',
        'nv_override',
        'nv_override_reason',
        'is_nv_final',
        'nilai',
        'peringkat',
        'sertifikat_path',
        'kartu_kendali',
        'laporan_visitasi_asesor1',
        'laporan_visitasi_asesor2',
        'laporan_visitasi_kelompok',
        'masa_berlaku',
        'masa_berlaku_akhir',
        'visitasi_confirmed_at',
        'catatan_visitasi',
        'catatan_rekomendasi_admin',
        'status_changed_at',
        'status_changed_by',
        'correction_cycle',
        'assessment_deadline',
    ];

    protected function casts(): array
    {
        return [
            'tgl_visitasi' => 'datetime',
            'tgl_visitasi_akhir' => 'datetime',
            'na1' => 'decimal:2',
            'na2' => 'decimal:2',
            'nk' => 'decimal:2',
            'nv' => 'decimal:2',
            'nilai' => 'decimal:2',
            'is_na1_final' => 'boolean',
            'is_na2_final' => 'boolean',
            'is_nk_final' => 'boolean',
            'is_nv_final' => 'boolean',
            'nv_override' => 'boolean',
            'visitasi_confirmed_at' => 'datetime',
            'masa_berlaku' => 'date',
            'masa_berlaku_akhir' => 'date',
            'status_changed_at' => 'datetime',
            'assessment_deadline' => 'datetime',
            'correction_cycle' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function rejections(): HasMany
    {
        return $this->hasMany(AkreditasiRejection::class);
    }

    public function bandings(): HasMany
    {
        return $this->hasMany(Banding::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AkreditasiAuditLog::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES, true);
    }

    public function isCorrectionStage(): bool
    {
        return in_array($this->status, self::CORRECTION_STATUSES, true);
    }

    public function getStatusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public static function getStatusLabelFor(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? $status;
    }

    public function getPermittedTransitions(): array
    {
        if (! isset(\App\Services\AkreditasiStateMachine::TRANSITIONS[$this->status])) {
            return [];
        }

        return \App\Services\AkreditasiStateMachine::TRANSITIONS[$this->status];
    }
}

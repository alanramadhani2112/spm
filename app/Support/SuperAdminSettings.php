<?php

namespace App\Support;

use App\Models\SuperAdminSetting;
use Illuminate\Support\Facades\Cache;

class SuperAdminSettings
{
    public const REVIEW_AWAL_DEADLINE = 'review_awal_deadline';

    public const ASSESSMENT_DEADLINE = 'assessment_deadline';

    public const REVIEW_TAHAP1_DEADLINE = 'review_tahap1_deadline';

    public const CORRECTION_TAHAP1_DEADLINE = 'correction_tahap1_deadline';

    public const REVIEW_TAHAP2_DEADLINE = 'review_tahap2_deadline';

    public const CORRECTION_TAHAP2_DEADLINE = 'correction_tahap2_deadline';

    public const SCORING_DEADLINE = 'scoring_deadline';

    public const BANDING_DEADLINE = 'banding_deadline';

    public const MAX_SIKLUS_TAHAP1 = 'max_siklus_tahap1';

    public const MAX_SIKLUS_TAHAP2 = 'max_siklus_tahap2';

    public const ACTION_ON_LIMIT = 'action_on_limit';

    public const KARTU_KENDALI_WAJIB_BEFORE = 'kartu_kendali_wajib_before';

    public const LAPORAN_WAJIB_BEFORE = 'laporan_wajib_before';

    public const NV_OVERRIDE_ALLOWED = 'nv_override_allowed';

    public const NV_REASON_MODE = 'nv_reason_mode';

    public const SUPERADMIN_RECEIVES_ADMIN_NOTIF = 'superadmin_receives_admin_notif';

    public const REMINDER_DAYS = 'reminder_days';

    public const BANDING_ELIGIBILITY = 'banding_eligibility';

    public const CATEGORIES = [
        'deadline' => [
            self::REVIEW_AWAL_DEADLINE,
            self::ASSESSMENT_DEADLINE,
            self::REVIEW_TAHAP1_DEADLINE,
            self::CORRECTION_TAHAP1_DEADLINE,
            self::REVIEW_TAHAP2_DEADLINE,
            self::CORRECTION_TAHAP2_DEADLINE,
            self::SCORING_DEADLINE,
            self::BANDING_DEADLINE,
        ],
        'correction' => [
            self::MAX_SIKLUS_TAHAP1,
            self::MAX_SIKLUS_TAHAP2,
            self::ACTION_ON_LIMIT,
        ],
        'document' => [
            self::KARTU_KENDALI_WAJIB_BEFORE,
            self::LAPORAN_WAJIB_BEFORE,
        ],
        'nv' => [
            self::NV_OVERRIDE_ALLOWED,
            self::NV_REASON_MODE,
        ],
        'notification' => [
            self::SUPERADMIN_RECEIVES_ADMIN_NOTIF,
            self::REMINDER_DAYS,
        ],
        'banding' => [
            self::BANDING_ELIGIBILITY,
        ],
    ];

    public const DEFAULTS = [
        self::REVIEW_AWAL_DEADLINE => 5,
        self::ASSESSMENT_DEADLINE => 14,
        self::REVIEW_TAHAP1_DEADLINE => 5,
        self::CORRECTION_TAHAP1_DEADLINE => 7,
        self::MAX_SIKLUS_TAHAP1 => 2,
        self::REVIEW_TAHAP2_DEADLINE => 5,
        self::CORRECTION_TAHAP2_DEADLINE => 7,
        self::MAX_SIKLUS_TAHAP2 => 2,
        self::SCORING_DEADLINE => 7,
        self::BANDING_DEADLINE => 7,
        self::KARTU_KENDALI_WAJIB_BEFORE => 'before_admin_validation',
        self::LAPORAN_WAJIB_BEFORE => 'before_submit',
        self::NV_OVERRIDE_ALLOWED => true,
        self::NV_REASON_MODE => 'collective',
        self::SUPERADMIN_RECEIVES_ADMIN_NOTIF => true,
        self::REMINDER_DAYS => 7,
        self::ACTION_ON_LIMIT => 'reject',
        self::BANDING_ELIGIBILITY => 'all',
    ];

    public const DEADLINE_PHASE_KEYS = [
        'initial_review' => self::REVIEW_AWAL_DEADLINE,
        'assessment_awal' => self::ASSESSMENT_DEADLINE,
        'admin_stage_1' => self::REVIEW_TAHAP1_DEADLINE,
        'stage_1_correction' => self::CORRECTION_TAHAP1_DEADLINE,
        'assessor_stage_2' => self::REVIEW_TAHAP2_DEADLINE,
        'stage_2_correction' => self::CORRECTION_TAHAP2_DEADLINE,
        'scoring' => self::SCORING_DEADLINE,
        'banding' => self::BANDING_DEADLINE,
    ];

    public static function allKeys(): array
    {
        return array_merge(...array_values(self::CATEGORIES));
    }

    public static function keysForCategory(string $category): array
    {
        return self::CATEGORIES[$category] ?? [];
    }

    public static function default(string $key): mixed
    {
        return self::DEFAULTS[$key] ?? null;
    }

    public static function get(string $key): mixed
    {
        return Cache::remember(self::cacheKey($key), 3600, function () use ($key) {
            $setting = SuperAdminSetting::where('key', $key)->first();

            return $setting?->value ?? self::default($key);
        });
    }

    public static function int(string $key): ?int
    {
        $value = self::get($key);

        return $value === null ? null : (int) $value;
    }

    public static function deadlineKeyForPhase(string $phase): ?string
    {
        return self::DEADLINE_PHASE_KEYS[$phase] ?? null;
    }

    public static function forget(string $key): void
    {
        Cache::forget(self::cacheKey($key));
    }

    private static function cacheKey(string $key): string
    {
        return "superadmin_setting:{$key}";
    }
}

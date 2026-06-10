<?php

namespace App\Services;

use App\Models\Akreditasi;
use App\Models\SuperAdminSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DeadlineService
{
    const DEFAULT_DEADLINES = [
        'initial_review' => 5,
        'assessment_awal' => 14,
        'admin_stage_1' => 5,
        'stage_1_correction' => 7,
        'assessor_stage_2' => 5,
        'stage_2_correction' => 7,
        'scoring' => 7,
    ];

    const PHASE_STATUS_MAP = [
        'initial_review' => [Akreditasi::STATUS_INITIAL_SUBMITTED],
        'assessment_awal' => [Akreditasi::STATUS_ASSESSMENT_OPEN],
        'admin_stage_1' => [Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW],
        'stage_1_correction' => [Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION],
        'assessor_stage_2' => [Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW],
        'stage_2_correction' => [Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION],
        'scoring' => [Akreditasi::STATUS_POST_VISITASI_SCORING],
    ];

    public function getDeadline(string $phase): ?Carbon
    {
        $days = $this->getDeadlineDays($phase);

        if ($days === null) {
            return null;
        }

        return Carbon::now()->addDays($days);
    }

    public function getOverdueAkreditasi(): Collection
    {
        $overdue = collect();
        $now = now();

        foreach (self::PHASE_STATUS_MAP as $phase => $statuses) {
            $days = $this->getDeadlineDays($phase);

            if ($days === null) {
                continue;
            }

            $cutoff = $now->copy()->subDays($days);

            $akreditasis = Akreditasi::whereIn('status', $statuses)
                ->where('status_changed_at', '<=', $cutoff)
                ->with('user')
                ->get();

            foreach ($akreditasis as $a) {
                $overdue->push($a);
            }
        }

        return $overdue;
    }

    public function isWithinDeadline(int $akreditasiId, string $phase): bool
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $days = $this->getDeadlineDays($phase);

        if ($days === null) {
            return true;
        }

        if (! $akreditasi->status_changed_at) {
            return true;
        }

        return $akreditasi->status_changed_at->addDays($days)->isFuture();
    }

    public function getRemainingDays(int $akreditasiId, string $phase): int
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $days = $this->getDeadlineDays($phase);

        if ($days === null || ! $akreditasi->status_changed_at) {
            return -1;
        }

        $deadline = $akreditasi->status_changed_at->copy()->addDays($days);

        return max(0, (int) now()->diffInDays($deadline, false));
    }

    public function getDeadlineDate(int $akreditasiId, string $phase): ?Carbon
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);
        $days = $this->getDeadlineDays($phase);

        if ($days === null || ! $akreditasi->status_changed_at) {
            return null;
        }

        return $akreditasi->status_changed_at->copy()->addDays($days);
    }

    private function getDeadlineDays(string $phase): ?int
    {
        $setting = Cache::remember("deadline_{$phase}", 3600, function () use ($phase) {
            $setting = SuperAdminSetting::where('key', "deadline_{$phase}")->first();

            if ($setting && $setting->value !== null) {
                return (int) $setting->value;
            }

            return self::DEFAULT_DEADLINES[$phase] ?? null;
        });

        return $setting;
    }
}

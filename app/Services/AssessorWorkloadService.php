<?php

namespace App\Services;

use App\Models\Akreditasi;
use App\Models\Assessment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AssessorWorkloadService
{
    private const MEDIUM_THRESHOLD = 3;

    private const HIGH_THRESHOLD = 5;

    public function rows(string $period = 'all'): Collection
    {
        return $this->forAssessors($this->assessors(), $period)
            ->values()
            ->sort(function (array $first, array $second) {
                if ($first['total'] !== $second['total']) {
                    return $second['total'] <=> $first['total'];
                }

                return strcmp($first['name'], $second['name']);
            })
            ->values();
    }

    public function forAssessors(Collection $asesors, string $period = 'all'): Collection
    {
        $period = $this->normalizePeriod($period);
        $assignmentsByAssessor = $this->activeAssignments($asesors, $period)->groupBy('asesor_id');

        return $asesors->mapWithKeys(function (User $asesor) use ($assignmentsByAssessor) {
            $assignments = $assignmentsByAssessor->get($asesor->id, collect())->values();
            $ketua = $assignments->where('tipe', 'ketua')->count();
            $anggota = $assignments->where('tipe', 'anggota')->count();
            $total = $assignments->count();
            $level = $this->level($total);

            return [$asesor->id => [
                'id' => $asesor->id,
                'name' => $asesor->name,
                'email' => $asesor->email,
                'total' => $total,
                'ketua' => $ketua,
                'anggota' => $anggota,
                'level' => $level,
                'color' => $this->color($level),
                'capacity_note' => $this->capacityNote($level),
                'overdue' => $assignments->filter(fn (Assessment $assessment) => $assessment->akreditasi?->assessment_deadline?->isPast())->count(),
                'status_counts' => $this->statusCounts($assignments),
                'latest_assignment' => $this->latestAssignment($assignments),
                'assignments' => $assignments,
            ]];
        });
    }

    public function dashboardRows(string $period = 'all', int $limit = 5): Collection
    {
        return $this->rows($period)
            ->filter(fn (array $row) => $row['total'] > 0)
            ->take($limit)
            ->map(fn (array $row) => [
                'id' => $row['id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'total' => $row['total'],
                'ketua' => $row['ketua'],
                'anggota' => $row['anggota'],
                'level' => $row['level'],
                'color' => $row['color'],
            ])
            ->values();
    }

    public function summary(Collection $rows): array
    {
        return [
            'total_asesor' => $rows->count(),
            'normal' => $rows->where('level', 'normal')->count(),
            'medium' => $rows->where('level', 'medium')->count(),
            'high' => $rows->where('level', 'high')->count(),
            'active_assignments' => $rows->sum('total'),
            'overdue_assignments' => $rows->sum('overdue'),
        ];
    }

    public function periodOptions(): array
    {
        $years = Akreditasi::query()
            ->orderByDesc('created_at')
            ->pluck('created_at')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->format('Y'))
            ->unique()
            ->mapWithKeys(fn ($year) => [(string) $year => (string) $year])
            ->all();

        return ['all' => 'Semua Periode'] + $years;
    }

    private function assessors(): Collection
    {
        return User::query()
            ->where('role_id', 2)
            ->orderBy('name')
            ->get();
    }

    private function activeAssignments(Collection $asesors, string $period): Collection
    {
        $assessorIds = $asesors->pluck('id')->filter()->values();

        if ($assessorIds->isEmpty()) {
            return collect();
        }

        return Assessment::query()
            ->with(['akreditasi.user.pesantren', 'asesor'])
            ->whereIn('asesor_id', $assessorIds)
            ->whereHas('akreditasi', function ($query) use ($period) {
                $query->whereNotIn('status', Akreditasi::TERMINAL_STATUSES)
                    ->when($period !== 'all', fn ($periodQuery) => $periodQuery->whereYear('created_at', (int) $period));
            })
            ->get();
    }

    private function statusCounts(Collection $assignments): Collection
    {
        return $assignments
            ->groupBy(fn (Assessment $assessment) => $assessment->akreditasi?->status ?? 'unknown')
            ->map(fn (Collection $items, string $status) => [
                'status' => $status,
                'label' => Akreditasi::STATUS_LABELS[$status] ?? $status,
                'color' => $this->statusColor($status),
                'total' => $items->count(),
            ])
            ->sortByDesc('total')
            ->values();
    }

    private function latestAssignment(Collection $assignments): ?Assessment
    {
        return $assignments
            ->sortByDesc(function (Assessment $assessment) {
                return $assessment->akreditasi?->status_changed_at
                    ?? $assessment->akreditasi?->created_at
                    ?? $assessment->created_at;
            })
            ->first();
    }

    private function level(int $total): string
    {
        return match (true) {
            $total >= self::HIGH_THRESHOLD => 'high',
            $total >= self::MEDIUM_THRESHOLD => 'medium',
            default => 'normal',
        };
    }

    private function color(string $level): string
    {
        return match ($level) {
            'high' => 'danger',
            'medium' => 'warning',
            default => 'success',
        };
    }

    private function capacityNote(string $level): string
    {
        return match ($level) {
            'high' => 'Overload',
            'medium' => 'Medium',
            default => 'Normal',
        };
    }

    private function statusColor(string $status): string
    {
        return match ($status) {
            Akreditasi::STATUS_INITIAL_SUBMITTED,
            Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED => 'primary',
            Akreditasi::STATUS_ASSESSMENT_OPEN,
            Akreditasi::STATUS_ASSESSOR_ASSIGNMENT,
            Akreditasi::STATUS_VISITASI_SCHEDULED,
            Akreditasi::STATUS_VISITASI_COMPLETED => 'info',
            Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
            Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION,
            Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW,
            Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
            Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION,
            Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW,
            Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
            Akreditasi::STATUS_APPEAL_SUBMITTED => 'warning',
            Akreditasi::STATUS_POST_VISITASI_SCORING,
            Akreditasi::STATUS_INITIAL_REJECTED,
            Akreditasi::STATUS_ADMINISTRATIVE_REJECTED,
            Akreditasi::STATUS_FINAL_REJECTED => 'danger',
            Akreditasi::STATUS_FINAL_APPROVED,
            Akreditasi::STATUS_COMPLETED => 'success',
            default => 'secondary',
        };
    }

    private function normalizePeriod(string $period): string
    {
        return preg_match('/^\d{4}$/', $period) === 1 ? $period : 'all';
    }
}

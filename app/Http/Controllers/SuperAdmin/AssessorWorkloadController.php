<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use App\Models\AkreditasiAuditLog;
use App\Models\Assessment;
use App\Models\User;
use App\Services\AssessorWorkloadService;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AssessorWorkloadController extends Controller
{
    public function __construct(
        private AssessorWorkloadService $workloadService,
        private AuditTrailService $auditTrail,
    ) {}

    public function index(Request $request): View
    {
        $period = $this->normalizePeriod((string) $request->query('period', 'all'));
        $load = $this->normalizeLoad((string) $request->query('load', 'all'));
        $rows = $this->workloadService->rows($period);
        $summary = $this->workloadService->summary($rows);
        $filteredRows = $this->filterRows($rows, $load);
        $periodOptions = $this->workloadService->periodOptions();
        $loadOptions = [
            'all' => 'Semua Beban',
            'normal' => 'Normal',
            'medium' => 'Medium',
            'high' => 'Overload',
        ];

        return view('superadmin.asesor-workload.index', compact(
            'period',
            'load',
            'rows',
            'filteredRows',
            'summary',
            'periodOptions',
            'loadOptions',
        ));
    }

    public function show(Request $request, User $asesor): View
    {
        abort_unless((int) $asesor->role_id === 2, 404);

        $period = $this->normalizePeriod((string) $request->query('period', 'all'));
        $load = $this->normalizeLoad((string) $request->query('load', 'all'));
        $periodOptions = $this->workloadService->periodOptions();
        $workload = $this->workloadService->forAssessors(collect([$asesor]), $period)->first();
        $heroStats = [
            'total' => $workload['total'],
            'ketua' => $workload['ketua'],
            'anggota' => $workload['anggota'],
            'overdue' => $workload['overdue'],
        ];
        $activeAssignments = $this->activeAssignmentsForWorkload($workload);
        $assignmentHistory = $this->assignmentHistoryForAssessor($asesor);

        return view('superadmin.asesor-workload.show', compact(
            'asesor',
            'period',
            'load',
            'periodOptions',
            'workload',
            'heroStats',
            'activeAssignments',
            'assignmentHistory',
        ));
    }

    public function export(Request $request)
    {
        $period = $this->normalizePeriod((string) $request->query('period', 'all'));
        $load = $this->normalizeLoad((string) $request->query('load', 'all'));
        $rows = $this->filterRows($this->workloadService->rows($period), $load);

        $this->auditTrail->log('superadmin_exported', null, auth()->id(), [
            'export_type' => 'assessor_workload',
            'format' => 'csv',
            'filters' => [
                'period' => $period,
                'load' => $load,
            ],
            'rows_exported' => $rows->count(),
        ]);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Asesor ID', 'Nama', 'Email', 'Assignment Aktif', 'Ketua', 'Anggota', 'Overdue', 'Load', 'Assignment Terbaru']);

            foreach ($rows as $row) {
                $latest = $row['latest_assignment'];
                $latestAkreditasi = $latest?->akreditasi;

                fputcsv($out, [
                    $row['id'],
                    $row['name'],
                    $row['email'],
                    $row['total'],
                    $row['ketua'],
                    $row['anggota'],
                    $row['overdue'],
                    $row['capacity_note'],
                    $latestAkreditasi?->uuid ?? '-',
                ]);
            }

            fclose($out);
        }, 'asesor-workload-superadmin.csv', ['Content-Type' => 'text/csv']);
    }

    private function assignmentLogsForAssessor(User $asesor): Collection
    {
        return AkreditasiAuditLog::query()
            ->with(['akreditasi.user.pesantren', 'user'])
            ->where('action_type', 'asesor_assigned')
            ->whereIn('metadata->assignment_context', ['superadmin_assign', 'superadmin_reassign'])
            ->latest()
            ->limit(200)
            ->get()
            ->filter(function (AkreditasiAuditLog $log) use ($asesor) {
                $assessorIds = $this->relatedAssessorIdsForLog($log);

                return $assessorIds->contains((int) $asesor->id);
            })
            ->values();
    }

    private function assignmentHistoryForAssessor(User $asesor): Collection
    {
        $logs = $this->assignmentLogsForAssessor($asesor);
        $assessorIds = $logs
            ->flatMap(fn (AkreditasiAuditLog $log) => $this->relatedAssessorIdsForLog($log))
            ->unique()
            ->values();
        $assessors = User::query()
            ->whereIn('id', $assessorIds->all())
            ->get()
            ->keyBy('id');

        return $logs
            ->map(fn (AkreditasiAuditLog $log) => $this->mapAssignmentHistoryItem($log, (int) $asesor->id, $assessors))
            ->values();
    }

    private function relatedAssessorIdsForLog(AkreditasiAuditLog $log): Collection
    {
        $metadata = $log->metadata ?? [];

        return collect([$metadata['ketua_id'] ?? null])
            ->merge($metadata['anggota_ids'] ?? [])
            ->merge(collect($metadata['previous_assignments'] ?? [])->pluck('asesor_id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    private function mapAssignmentHistoryItem(AkreditasiAuditLog $log, int $assessorId, Collection $assessors): array
    {
        $metadata = $log->metadata ?? [];
        $currentKetuaId = (int) ($metadata['ketua_id'] ?? 0);
        $currentAnggotaIds = collect($metadata['anggota_ids'] ?? [])->map(fn ($id) => (int) $id);
        $currentRole = $currentKetuaId === $assessorId
            ? 'ketua'
            : ($currentAnggotaIds->contains($assessorId) ? 'anggota' : null);
        $currentKetua = $currentKetuaId > 0 ? $assessors->get($currentKetuaId) : null;
        $currentAnggota = $currentAnggotaIds
            ->map(fn (int $id) => $assessors->get($id))
            ->filter()
            ->values();
        $previousTeam = collect($metadata['previous_assignments'] ?? [])
            ->map(function (array $assignment) use ($assessors) {
                $previousAssessorId = (int) ($assignment['asesor_id'] ?? 0);
                $previousAssessor = $previousAssessorId > 0 ? $assessors->get($previousAssessorId) : null;
                $role = $assignment['tipe'] ?? null;

                return [
                    'asesor_id' => $previousAssessorId,
                    'name' => $assignment['name'] ?? $previousAssessor?->name ?? 'Asesor',
                    'email' => $assignment['email'] ?? $previousAssessor?->email,
                    'tipe' => $role,
                    'role_label' => $this->assessorRoleLabel($role),
                ];
            })
            ->values();
        $previousRole = $previousTeam->firstWhere('asesor_id', $assessorId)['tipe'] ?? null;

        return [
            'log' => $log,
            'label' => ($metadata['assignment_context'] ?? null) === 'superadmin_reassign' || $previousTeam->isNotEmpty()
                ? 'Reassignment'
                : 'Assignment',
            'akreditasi' => $log->akreditasi,
            'pesantren_name' => $log->akreditasi?->user?->pesantren?->nama_pesantren ?? $log->akreditasi?->user?->name ?? 'Pesantren',
            'actor_name' => $log->user?->name ?? 'Sistem',
            'timestamp' => $log->created_at,
            'assessor_role' => $currentRole ?? $previousRole,
            'assessor_role_label' => $this->assessorRoleLabel($currentRole ?? $previousRole),
            'current_team' => [
                'ketua' => $currentKetua,
                'anggota' => $currentAnggota,
            ],
            'previous_team' => $previousTeam,
            'reason' => $log->reason,
            'overload_warnings' => collect($metadata['overload_warnings'] ?? []),
            'detail_url' => $log->akreditasi ? route('superadmin.akreditasi.show', $log->akreditasi) : null,
        ];
    }

    private function activeAssignmentsForWorkload(array $workload): Collection
    {
        return collect($workload['assignments'] ?? [])
            ->sort(function (Assessment $first, Assessment $second) {
                $firstOverdue = $first->akreditasi?->assessment_deadline?->isPast() ? 1 : 0;
                $secondOverdue = $second->akreditasi?->assessment_deadline?->isPast() ? 1 : 0;

                if ($firstOverdue !== $secondOverdue) {
                    return $secondOverdue <=> $firstOverdue;
                }

                $firstTimestamp = $first->akreditasi?->status_changed_at?->getTimestamp()
                    ?? $first->akreditasi?->created_at?->getTimestamp()
                    ?? $first->created_at?->getTimestamp()
                    ?? 0;
                $secondTimestamp = $second->akreditasi?->status_changed_at?->getTimestamp()
                    ?? $second->akreditasi?->created_at?->getTimestamp()
                    ?? $second->created_at?->getTimestamp()
                    ?? 0;

                return $secondTimestamp <=> $firstTimestamp;
            })
            ->map(function (Assessment $assessment) {
                $akreditasi = $assessment->akreditasi;
                $isOverdue = (bool) $akreditasi?->assessment_deadline?->isPast();

                return [
                    'assessment' => $assessment,
                    'akreditasi' => $akreditasi,
                    'pesantren_name' => $akreditasi?->user?->pesantren?->nama_pesantren ?? $akreditasi?->user?->name ?? 'Pesantren',
                    'role' => $assessment->tipe,
                    'role_label' => $this->assessorRoleLabel($assessment->tipe),
                    'status' => $akreditasi?->status,
                    'status_label' => $akreditasi?->getStatusLabel() ?? 'Status belum tersedia',
                    'status_color' => $this->workloadStatusColor($akreditasi?->status),
                    'deadline_label' => $akreditasi?->assessment_deadline?->format('d M Y') ?? '—',
                    'is_overdue' => $isOverdue,
                    'detail_url' => $akreditasi ? route('superadmin.akreditasi.show', $akreditasi) : null,
                ];
            })
            ->values();
    }

    private function assessorRoleLabel(?string $role): string
    {
        return match ($role) {
            'ketua' => 'Ketua',
            'anggota' => 'Anggota',
            default => 'Tidak tercatat',
        };
    }

    private function workloadStatusColor(?string $status): string
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

    private function filterRows(Collection $rows, string $load): Collection
    {
        return $load === 'all'
            ? $rows
            : $rows->where('level', $load)->values();
    }

    private function normalizePeriod(string $period): string
    {
        return preg_match('/^\d{4}$/', $period) === 1 ? $period : 'all';
    }

    private function normalizeLoad(string $load): string
    {
        return in_array($load, ['normal', 'medium', 'high'], true) ? $load : 'all';
    }
}

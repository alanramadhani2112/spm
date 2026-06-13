<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use App\Models\Assessment;
use App\Services\AuditTrailService;
use App\Support\SuperAdminSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private AuditTrailService $auditTrail,
    ) {}

    public function index(Request $request)
    {
        $period = $request->query('period', 'all');
        $baseQuery = $this->dashboardQuery($period);

        $totalAkreditasi = (clone $baseQuery)->count();
        $activeAkreditasi = (clone $baseQuery)->whereNotIn('status', Akreditasi::TERMINAL_STATUSES)->count();
        $completedAkreditasi = (clone $baseQuery)->whereIn('status', Akreditasi::TERMINAL_STATUSES)->count();

        $byStatus = (clone $baseQuery)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->mapWithKeys(fn ($total, $status) => [$status => [
                'total' => $total,
                'label' => Akreditasi::STATUS_LABELS[$status] ?? $status,
            ]]);

        $overdueCount = (clone $baseQuery)->whereNotIn('status', Akreditasi::TERMINAL_STATUSES)
            ->whereNotNull('assessment_deadline')
            ->where('assessment_deadline', '<', now())
            ->count();

        $periodOptions = $this->periodOptions();
        $statusColors = $this->statusColors();
        $priorityCards = $this->priorityCards($baseQuery, $overdueCount);
        $operationalQueues = $this->operationalQueues($baseQuery, $period);
        $slaBreaches = $this->slaBreaches($baseQuery, $period);
        $assessorWorkloads = $this->assessorWorkloads($period);
        $urgentAkreditasis = $this->urgentAkreditasis($baseQuery);
        $recentAkreditasis = (clone $baseQuery)
            ->with('user.pesantren')
            ->latest()
            ->limit(5)
            ->get();

        return view('superadmin.dashboard.index', compact(
            'totalAkreditasi',
            'activeAkreditasi',
            'completedAkreditasi',
            'byStatus',
            'overdueCount',
            'period',
            'periodOptions',
            'statusColors',
            'priorityCards',
            'operationalQueues',
            'slaBreaches',
            'assessorWorkloads',
            'urgentAkreditasis',
            'recentAkreditasis',
        ));
    }

    public function export(Request $request)
    {
        $period = $request->query('period', 'all');
        $rows = $this->dashboardQuery($period)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $this->auditTrail->log('superadmin_exported', null, auth()->id(), [
            'export_type' => 'dashboard_summary',
            'format' => 'csv',
            'filters' => [
                'period' => $period,
            ],
            'rows_exported' => (int) $rows->sum('total'),
            'grouped_rows' => $rows->count(),
        ]);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Status', 'Label', 'Total']);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->status,
                    Akreditasi::STATUS_LABELS[$row->status] ?? $row->status,
                    $row->total,
                ]);
            }

            fclose($out);
        }, 'dashboard-superadmin.csv', ['Content-Type' => 'text/csv']);
    }

    private function dashboardQuery(string $period)
    {
        $query = Akreditasi::query();

        if ($period !== 'all') {
            $query->whereYear('created_at', (int) $period);
        }

        return $query;
    }

    private function periodOptions(): array
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

    private function priorityCards($baseQuery, int $overdueCount): array
    {
        return [
            [
                'label' => 'Pengajuan Baru',
                'description' => 'Butuh review awal Super Admin/Admin.',
                'count' => (clone $baseQuery)->where('status', Akreditasi::STATUS_INITIAL_SUBMITTED)->count(),
                'color' => 'primary',
                'icon' => 'ki-add-files',
                'route' => route('superadmin.akreditasi.index', ['status' => Akreditasi::STATUS_INITIAL_SUBMITTED]),
            ],
            [
                'label' => 'Review Tahap 1',
                'description' => 'Data administrasi menunggu keputusan.',
                'count' => (clone $baseQuery)->whereIn('status', [
                    Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
                    Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW,
                ])->count(),
                'color' => 'warning',
                'icon' => 'ki-notepad-edit',
                'route' => route('superadmin.akreditasi.index', ['status' => Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW]),
            ],
            [
                'label' => 'Penugasan Asesor',
                'description' => 'Perlu ditentukan ketua dan anggota asesor.',
                'count' => (clone $baseQuery)->where('status', Akreditasi::STATUS_ASSESSOR_ASSIGNMENT)->count(),
                'color' => 'info',
                'icon' => 'ki-people',
                'route' => route('superadmin.akreditasi.index', ['status' => Akreditasi::STATUS_ASSESSOR_ASSIGNMENT]),
            ],
            [
                'label' => 'Validasi Akhir',
                'description' => 'Hasil visitasi siap divalidasi.',
                'count' => (clone $baseQuery)->whereIn('status', [
                    Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED,
                    Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
                ])->count(),
                'color' => 'success',
                'icon' => 'ki-shield-tick',
                'route' => route('superadmin.akreditasi.index', ['status' => Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED]),
            ],
            [
                'label' => 'Overdue',
                'description' => 'Deadline assessment sudah melewati batas.',
                'count' => $overdueCount,
                'color' => 'danger',
                'icon' => 'ki-warning-2',
                'route' => route('superadmin.akreditasi.index'),
            ],
        ];
    }

    private function operationalQueues($baseQuery, string $period): array
    {
        return [
            $this->queueCard($baseQuery, 'Review Awal', 'Pengajuan baru menunggu keputusan.', [Akreditasi::STATUS_INITIAL_SUBMITTED], 'primary', 'ki-search-list', $period),
            $this->queueCard($baseQuery, 'Tahap 1', 'Administrasi dan limit koreksi tahap 1.', [
                Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
                Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW,
            ], 'warning', 'ki-notepad-edit', $period),
            $this->queueCard($baseQuery, 'Assign Asesor', 'Belum punya tim asesor aktif.', [Akreditasi::STATUS_ASSESSOR_ASSIGNMENT], 'info', 'ki-people', $period),
            $this->queueCard($baseQuery, 'Tahap 2', 'Review asesor dan limit koreksi tahap 2.', [
                Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
                Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW,
            ], 'warning', 'ki-teacher', $period),
            $this->queueCard($baseQuery, 'Visitasi', 'Jadwal visitasi yang perlu dipantau.', [Akreditasi::STATUS_VISITASI_SCHEDULED], 'info', 'ki-calendar-tick', $period),
            $this->queueCard($baseQuery, 'Scoring', 'NA1, NA2, NK, dan laporan visitasi.', [Akreditasi::STATUS_POST_VISITASI_SCORING], 'danger', 'ki-chart-line', $period),
            $this->queueCard($baseQuery, 'Validasi Akhir', 'Hasil visitasi siap difinalisasi.', [
                Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED,
                Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
            ], 'success', 'ki-shield-tick', $period),
            $this->queueCard($baseQuery, 'Terbitkan SK', 'Final approved belum selesai/SK.', [Akreditasi::STATUS_FINAL_APPROVED], 'success', 'ki-award', $period),
            $this->queueCard($baseQuery, 'Banding', 'Permohonan banding perlu keputusan.', [Akreditasi::STATUS_APPEAL_SUBMITTED], 'warning', 'ki-message-question', $period),
        ];
    }

    private function queueCard($baseQuery, string $label, string $description, array $statuses, string $color, string $icon, string $period): array
    {
        $count = (clone $baseQuery)->whereIn('status', $statuses)->count();
        $status = count($statuses) === 1 ? $statuses[0] : $statuses[0];

        return [
            'label' => $label,
            'description' => $description,
            'count' => $count,
            'color' => $color,
            'icon' => $icon,
            'route' => route('superadmin.akreditasi.index', [
                'period' => $period,
                'status' => $status,
            ]),
        ];
    }

    private function slaBreaches($baseQuery, string $period): array
    {
        $phaseMap = [
            'initial_review' => [
                'label' => 'Review Awal',
                'statuses' => [Akreditasi::STATUS_INITIAL_SUBMITTED],
            ],
            'assessment_awal' => [
                'label' => 'Assessment',
                'statuses' => [Akreditasi::STATUS_ASSESSMENT_OPEN],
            ],
            'admin_stage_1' => [
                'label' => 'Review Tahap 1',
                'statuses' => [Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW],
            ],
            'stage_1_correction' => [
                'label' => 'Koreksi Tahap 1',
                'statuses' => [Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION],
            ],
            'assessor_stage_2' => [
                'label' => 'Review Tahap 2',
                'statuses' => [Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW],
            ],
            'stage_2_correction' => [
                'label' => 'Koreksi Tahap 2',
                'statuses' => [Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION],
            ],
            'scoring' => [
                'label' => 'Scoring',
                'statuses' => [Akreditasi::STATUS_POST_VISITASI_SCORING],
            ],
        ];

        return collect($phaseMap)
            ->map(function (array $phase, string $phaseKey) use ($baseQuery, $period) {
                $settingKey = SuperAdminSettings::deadlineKeyForPhase($phaseKey);
                $days = $settingKey ? SuperAdminSettings::int($settingKey) : null;

                if ($days === null) {
                    $count = 0;
                } else {
                    $cutoff = now()->subDays($days);
                    $count = (clone $baseQuery)
                        ->whereIn('status', $phase['statuses'])
                        ->where(function ($query) use ($cutoff) {
                            $query->where('status_changed_at', '<=', $cutoff)
                                ->orWhere(function ($fallbackQuery) use ($cutoff) {
                                    $fallbackQuery->whereNull('status_changed_at')
                                        ->where('created_at', '<=', $cutoff);
                                });
                        })
                        ->count();
                }

                return [
                    'label' => $phase['label'],
                    'days' => $days,
                    'count' => $count,
                    'route' => route('superadmin.akreditasi.index', [
                        'period' => $period,
                        'status' => $phase['statuses'][0],
                    ]),
                ];
            })
            ->values()
            ->all();
    }

    private function assessorWorkloads(string $period)
    {
        return Assessment::query()
            ->with(['asesor', 'akreditasi'])
            ->whereHas('akreditasi', function ($query) use ($period) {
                $query->whereNotIn('status', Akreditasi::TERMINAL_STATUSES)
                    ->when($period !== 'all', fn ($periodQuery) => $periodQuery->whereYear('created_at', (int) $period));
            })
            ->get()
            ->groupBy('asesor_id')
            ->map(function ($assignments) {
                $asesor = $assignments->first()?->asesor;

                return [
                    'name' => $asesor?->name ?? 'Asesor',
                    'email' => $asesor?->email,
                    'total' => $assignments->count(),
                    'ketua' => $assignments->where('tipe', 'ketua')->count(),
                    'anggota' => $assignments->where('tipe', 'anggota')->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();
    }

    private function urgentAkreditasis($baseQuery)
    {
        $priorityStatuses = [
            Akreditasi::STATUS_INITIAL_SUBMITTED,
            Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
            Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW,
            Akreditasi::STATUS_ASSESSOR_ASSIGNMENT,
            Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
            Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW,
            Akreditasi::STATUS_POST_VISITASI_SCORING,
            Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED,
            Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
            Akreditasi::STATUS_FINAL_APPROVED,
            Akreditasi::STATUS_APPEAL_SUBMITTED,
        ];

        return (clone $baseQuery)
            ->with(['user.pesantren', 'bandings'])
            ->whereIn('status', $priorityStatuses)
            ->orderByRaw('COALESCE(status_changed_at, created_at) asc')
            ->limit(6)
            ->get();
    }

    private function statusColors(): array
    {
        return [
            Akreditasi::STATUS_DRAFT_PROFILE => 'secondary',
            Akreditasi::STATUS_INITIAL_SUBMITTED => 'primary',
            Akreditasi::STATUS_ASSESSMENT_OPEN => 'info',
            Akreditasi::STATUS_INITIAL_REJECTED => 'danger',
            Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW => 'warning',
            Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION => 'warning',
            Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW => 'warning',
            Akreditasi::STATUS_ASSESSOR_ASSIGNMENT => 'info',
            Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW => 'warning',
            Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION => 'warning',
            Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW => 'warning',
            Akreditasi::STATUS_VISITASI_SCHEDULED => 'info',
            Akreditasi::STATUS_VISITASI_COMPLETED => 'info',
            Akreditasi::STATUS_POST_VISITASI_SCORING => 'danger',
            Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED => 'primary',
            Akreditasi::STATUS_ADMIN_FINAL_VALIDATION => 'warning',
            Akreditasi::STATUS_ADMINISTRATIVE_REJECTED => 'danger',
            Akreditasi::STATUS_FINAL_APPROVED => 'success',
            Akreditasi::STATUS_FINAL_REJECTED => 'danger',
            Akreditasi::STATUS_APPEAL_SUBMITTED => 'warning',
            Akreditasi::STATUS_COMPLETED => 'success',
        ];
    }
}

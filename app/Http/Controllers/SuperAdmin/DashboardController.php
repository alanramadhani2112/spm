<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use App\Services\AssessorWorkloadService;
use App\Services\AuditTrailService;
use App\Support\SuperAdminSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __construct(
        private AuditTrailService $auditTrail,
        private AssessorWorkloadService $assessorWorkloadService,
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

        $pipelineSteps = $this->pipelineSteps($baseQuery);
        $pipelineActiveIndex = $this->pipelineActiveIndex($pipelineSteps);
        $stats = $byStatus;

        return view('superadmin.dashboard.index', compact(
            'totalAkreditasi',
            'activeAkreditasi',
            'completedAkreditasi',
            'byStatus',
            'stats',
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
            'pipelineSteps',
            'pipelineActiveIndex',
        ));
    }

    public function visitasiOverview(Request $request)
    {
        $period = (string) $request->query('period', 'all');
        $status = $this->normalizeVisitasiStatus((string) $request->query('status', 'all'));
        $schedule = $this->normalizeVisitasiSchedule((string) $request->query('schedule', 'all'));
        $search = trim((string) $request->query('q', ''));
        $periodOptions = $this->periodOptions();
        $statusColors = $this->statusColors();
        $statusOptions = $this->visitasiStatusOptions();
        $scheduleOptions = $this->visitasiScheduleOptions();
        $baseQuery = $this->visitasiOverviewBaseQuery($period, $search);
        $summary = $this->visitasiSummary(clone $baseQuery);
        $nextStepLabels = $this->visitasiNextStepLabels();
        $akreditasis = $this->applyVisitasiOverviewFilters(clone $baseQuery, $status, $schedule)
            ->with(['user.pesantren', 'assessments.asesor'])
            ->orderByRaw("CASE status
                WHEN 'assessor_stage_2_review' THEN 1
                WHEN 'visitasi_scheduled' THEN 2
                WHEN 'post_visitasi_scoring' THEN 3
                WHEN 'visitasi_result_submitted' THEN 4
                WHEN 'admin_final_validation' THEN 5
                ELSE 99
            END")
            ->orderByRaw('CASE WHEN tgl_visitasi IS NULL THEN 1 ELSE 0 END')
            ->orderBy('tgl_visitasi')
            ->orderBy('tgl_visitasi_akhir')
            ->orderByRaw('COALESCE(status_changed_at, created_at) asc')
            ->get();
        $visitasiRows = $this->visitasiRows($akreditasis);
        $displayedCount = $visitasiRows->count();

        return view('superadmin.visitasi.index', compact(
            'period',
            'status',
            'schedule',
            'search',
            'periodOptions',
            'statusColors',
            'statusOptions',
            'scheduleOptions',
            'summary',
            'nextStepLabels',
            'visitasiRows',
            'displayedCount',
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

    private function visitasiOverviewBaseQuery(string $period, string $search)
    {
        return Akreditasi::query()
            ->whereIn('status', [
                Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
                Akreditasi::STATUS_VISITASI_SCHEDULED,
                Akreditasi::STATUS_POST_VISITASI_SCORING,
                Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED,
                Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
            ])
            ->when($period !== 'all', fn ($query) => $query->whereYear('created_at', (int) $period))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('uuid', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('user.pesantren', fn ($pesantrenQuery) => $pesantrenQuery
                            ->where('nama_pesantren', 'like', "%{$search}%")
                            ->orWhere('ns_pesantren', 'like', "%{$search}%"));
                });
            });
    }

    private function applyVisitasiOverviewFilters($query, string $status, string $schedule)
    {
        $today = now()->startOfDay();

        return $query
            ->when($status !== 'all', fn ($filteredQuery) => $filteredQuery->where('status', $status))
            ->when($schedule === 'unscheduled', fn ($filteredQuery) => $filteredQuery->where('status', Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW))
            ->when($schedule === 'upcoming', fn ($filteredQuery) => $filteredQuery
                ->where('status', Akreditasi::STATUS_VISITASI_SCHEDULED)
                ->whereDate('tgl_visitasi', '>', $today))
            ->when($schedule === 'ongoing', fn ($filteredQuery) => $filteredQuery
                ->where('status', Akreditasi::STATUS_VISITASI_SCHEDULED)
                ->whereDate('tgl_visitasi', '<=', $today)
                ->whereDate('tgl_visitasi_akhir', '>=', $today))
            ->when($schedule === 'past_due', fn ($filteredQuery) => $filteredQuery
                ->where('status', Akreditasi::STATUS_VISITASI_SCHEDULED)
                ->whereDate('tgl_visitasi_akhir', '<', $today));
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
            [
                'label' => 'Visitasi',
                'description' => 'Jadwal visitasi yang perlu dipantau.',
                'count' => (clone $baseQuery)->where('status', Akreditasi::STATUS_VISITASI_SCHEDULED)->count(),
                'color' => 'info',
                'icon' => 'ki-calendar-tick',
                'route' => route('superadmin.visitasi.index', [
                    'period' => $period,
                    'status' => Akreditasi::STATUS_VISITASI_SCHEDULED,
                ]),
            ],
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
        return $this->assessorWorkloadService->dashboardRows($period);
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

    private function visitasiStatusOptions(): array
    {
        return [
            Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW => 'Siap Dijadwalkan',
            Akreditasi::STATUS_VISITASI_SCHEDULED => Akreditasi::STATUS_LABELS[Akreditasi::STATUS_VISITASI_SCHEDULED],
            Akreditasi::STATUS_POST_VISITASI_SCORING => Akreditasi::STATUS_LABELS[Akreditasi::STATUS_POST_VISITASI_SCORING],
            Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED => Akreditasi::STATUS_LABELS[Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED],
            Akreditasi::STATUS_ADMIN_FINAL_VALIDATION => Akreditasi::STATUS_LABELS[Akreditasi::STATUS_ADMIN_FINAL_VALIDATION],
        ];
    }

    private function visitasiScheduleOptions(): array
    {
        return [
            'all' => 'Semua Window Jadwal',
            'unscheduled' => 'Belum Dijadwalkan',
            'upcoming' => 'Akan Datang',
            'ongoing' => 'Sedang Berjalan',
            'past_due' => 'Lewat Jadwal',
        ];
    }

    private function normalizeVisitasiStatus(string $status): string
    {
        return $status === 'all' || array_key_exists($status, $this->visitasiStatusOptions())
            ? $status
            : 'all';
    }

    private function normalizeVisitasiSchedule(string $schedule): string
    {
        return array_key_exists($schedule, $this->visitasiScheduleOptions())
            ? $schedule
            : 'all';
    }

    private function visitasiSummary($baseQuery): array
    {
        $today = now()->startOfDay();

        return [
            'ready' => (clone $baseQuery)->where('status', Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW)->count(),
            'scheduled' => (clone $baseQuery)->where('status', Akreditasi::STATUS_VISITASI_SCHEDULED)->count(),
            'ongoing' => (clone $baseQuery)
                ->where('status', Akreditasi::STATUS_VISITASI_SCHEDULED)
                ->whereDate('tgl_visitasi', '<=', $today)
                ->whereDate('tgl_visitasi_akhir', '>=', $today)
                ->count(),
            'past_due' => (clone $baseQuery)
                ->where('status', Akreditasi::STATUS_VISITASI_SCHEDULED)
                ->whereDate('tgl_visitasi_akhir', '<', $today)
                ->count(),
            'scoring' => (clone $baseQuery)->where('status', Akreditasi::STATUS_POST_VISITASI_SCORING)->count(),
            'validation' => (clone $baseQuery)->whereIn('status', [
                Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED,
                Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
            ])->count(),
        ];
    }

    private function visitasiNextStepLabels(): array
    {
        return [
            Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW => 'Tetapkan jadwal visitasi dan catatan pelaksanaan.',
            Akreditasi::STATUS_VISITASI_SCHEDULED => 'Pantau jadwal dan pastikan visitasi selesai sesuai rencana.',
            Akreditasi::STATUS_POST_VISITASI_SCORING => 'Lengkapi NA1, NA2, NK, dan laporan visitasi.',
            Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED => 'Masuk ke validasi akhir hasil visitasi.',
            Akreditasi::STATUS_ADMIN_FINAL_VALIDATION => 'Finalisasi keputusan validasi akhir.',
        ];
    }

    private function visitasiRows(Collection $akreditasis): Collection
    {
        return $akreditasis
            ->map(fn (Akreditasi $akreditasi) => $this->visitasiRow($akreditasi))
            ->values();
    }

    private function visitasiRow(Akreditasi $akreditasi): array
    {
        $scheduleState = $this->visitasiScheduleState($akreditasi);
        $team = $this->visitasiTeam($akreditasi);

        return [
            'akreditasi' => $akreditasi,
            'status_label' => $akreditasi->getStatusLabel(),
            'status_color' => $this->statusColors()[$akreditasi->status] ?? 'secondary',
            'next_step' => $this->visitasiNextStepLabels()[$akreditasi->status] ?? 'Pantau progress visitasi.',
            'pesantren_name' => $akreditasi->user?->pesantren?->nama_pesantren ?? $akreditasi->user?->name ?? 'Pesantren',
            'pesantren_email' => $akreditasi->user?->email,
            'pesantren_nsp' => $akreditasi->user?->pesantren?->ns_pesantren,
            'schedule_state' => $scheduleState,
            'schedule_range' => $akreditasi->tgl_visitasi
                ? $akreditasi->tgl_visitasi->format('d M Y').($akreditasi->tgl_visitasi_akhir ? ' — '.$akreditasi->tgl_visitasi_akhir->format('d M Y') : '')
                : 'Belum dijadwalkan',
            'team' => $team,
            'catatan_preview' => filled($akreditasi->catatan_visitasi)
                ? Str::limit((string) $akreditasi->catatan_visitasi, 100)
                : 'Belum ada catatan visitasi.',
            'progress' => $this->visitasiProgressItems($akreditasi),
            'actions' => $this->visitasiActions($akreditasi),
        ];
    }

    private function visitasiScheduleState(Akreditasi $akreditasi): array
    {
        $today = now()->startOfDay();

        if ($akreditasi->status === Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW) {
            return [
                'label' => 'Belum Dijadwalkan',
                'color' => 'warning',
            ];
        }

        if ($akreditasi->status === Akreditasi::STATUS_VISITASI_SCHEDULED) {
            if (! $akreditasi->tgl_visitasi || ! $akreditasi->tgl_visitasi_akhir) {
                return [
                    'label' => 'Jadwal Belum Lengkap',
                    'color' => 'warning',
                ];
            }

            if ($akreditasi->tgl_visitasi_akhir->lt($today)) {
                return [
                    'label' => 'Lewat Jadwal',
                    'color' => 'danger',
                ];
            }

            if ($akreditasi->tgl_visitasi->gt($today)) {
                return [
                    'label' => 'Akan Datang',
                    'color' => 'info',
                ];
            }

            return [
                'label' => 'Sedang Berjalan',
                'color' => 'primary',
            ];
        }

        if ($akreditasi->status === Akreditasi::STATUS_POST_VISITASI_SCORING) {
            return [
                'label' => 'Masuk Scoring',
                'color' => 'danger',
            ];
        }

        return [
            'label' => 'Siap Validasi',
            'color' => 'success',
        ];
    }

    private function visitasiTeam(Akreditasi $akreditasi): array
    {
        $ketua = $akreditasi->assessments->firstWhere('tipe', 'ketua');
        $anggota = $akreditasi->assessments
            ->where('tipe', 'anggota')
            ->map(fn ($assessment) => $assessment->asesor?->name)
            ->filter()
            ->values();

        return [
            'ketua' => $ketua?->asesor?->name,
            'anggota' => $anggota,
        ];
    }

    private function visitasiProgressItems(Akreditasi $akreditasi): array
    {
        return [
            ['label' => 'NA1', 'done' => filled($akreditasi->na1)],
            ['label' => 'NA2', 'done' => filled($akreditasi->na2)],
            ['label' => 'NK', 'done' => filled($akreditasi->nk)],
            ['label' => 'Lap. A1', 'done' => filled($akreditasi->laporan_visitasi_asesor1)],
            ['label' => 'Lap. A2', 'done' => filled($akreditasi->laporan_visitasi_asesor2)],
            ['label' => 'Lap. Kelompok', 'done' => filled($akreditasi->laporan_visitasi_kelompok)],
        ];
    }

    private function pipelineSteps($baseQuery): array
    {
        $counts = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            [
                'id' => 1,
                'label' => 'Pengajuan',
                'icon' => 'ki-add-files',
                'color' => 'primary',
                'statusFilters' => [
                    Akreditasi::STATUS_DRAFT_PROFILE,
                    Akreditasi::STATUS_INITIAL_SUBMITTED,
                ],
                'count' => (int) ($counts[Akreditasi::STATUS_DRAFT_PROFILE] ?? 0)
                    + (int) ($counts[Akreditasi::STATUS_INITIAL_SUBMITTED] ?? 0),
            ],
            [
                'id' => 2,
                'label' => 'Review Awal',
                'icon' => 'ki-magnifier',
                'color' => 'info',
                'statusFilters' => [
                    Akreditasi::STATUS_INITIAL_REJECTED,
                    Akreditasi::STATUS_ASSESSMENT_OPEN,
                ],
                'count' => (int) ($counts[Akreditasi::STATUS_INITIAL_REJECTED] ?? 0)
                    + (int) ($counts[Akreditasi::STATUS_ASSESSMENT_OPEN] ?? 0),
            ],
            [
                'id' => 3,
                'label' => 'Assessment',
                'icon' => 'ki-notepad-edit',
                'color' => 'warning',
                'statusFilters' => [
                    Akreditasi::STATUS_ASSESSMENT_OPEN,
                ],
                'count' => (clone $baseQuery)
                    ->where('status', Akreditasi::STATUS_ASSESSMENT_OPEN)
                    ->whereNotNull('assessment_deadline')
                    ->where('assessment_deadline', '>=', now())
                    ->count(),
            ],
            [
                'id' => 4,
                'label' => 'Review Tahap 1',
                'icon' => 'ki-verify',
                'color' => 'primary',
                'statusFilters' => [
                    Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
                    Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION,
                    Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW,
                ],
                'count' => (int) ($counts[Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW] ?? 0)
                    + (int) ($counts[Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION] ?? 0)
                    + (int) ($counts[Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW] ?? 0),
            ],
            [
                'id' => 5,
                'label' => 'Assign Asesor',
                'icon' => 'ki-people',
                'color' => 'info',
                'statusFilters' => [
                    Akreditasi::STATUS_ASSESSOR_ASSIGNMENT,
                ],
                'count' => (int) ($counts[Akreditasi::STATUS_ASSESSOR_ASSIGNMENT] ?? 0),
            ],
            [
                'id' => 6,
                'label' => 'Visitasi',
                'icon' => 'ki-geolocation',
                'color' => 'success',
                'statusFilters' => [
                    Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
                    Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION,
                    Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW,
                    Akreditasi::STATUS_VISITASI_SCHEDULED,
                    Akreditasi::STATUS_VISITASI_COMPLETED,
                ],
                'count' => (int) ($counts[Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW] ?? 0)
                    + (int) ($counts[Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION] ?? 0)
                    + (int) ($counts[Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW] ?? 0)
                    + (int) ($counts[Akreditasi::STATUS_VISITASI_SCHEDULED] ?? 0)
                    + (int) ($counts[Akreditasi::STATUS_VISITASI_COMPLETED] ?? 0),
            ],
            [
                'id' => 7,
                'label' => 'Scoring',
                'icon' => 'ki-chart-line',
                'color' => 'warning',
                'statusFilters' => [
                    Akreditasi::STATUS_POST_VISITASI_SCORING,
                    Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED,
                ],
                'count' => (int) ($counts[Akreditasi::STATUS_POST_VISITASI_SCORING] ?? 0)
                    + (int) ($counts[Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED] ?? 0),
            ],
            [
                'id' => 8,
                'label' => 'Validasi Akhir',
                'icon' => 'ki-shield-tick',
                'color' => 'danger',
                'statusFilters' => [
                    Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
                    Akreditasi::STATUS_ADMINISTRATIVE_REJECTED,
                ],
                'count' => (int) ($counts[Akreditasi::STATUS_ADMIN_FINAL_VALIDATION] ?? 0)
                    + (int) ($counts[Akreditasi::STATUS_ADMINISTRATIVE_REJECTED] ?? 0),
            ],
            [
                'id' => 9,
                'label' => 'SK',
                'icon' => 'ki-medal-star',
                'color' => 'success',
                'statusFilters' => [
                    Akreditasi::STATUS_FINAL_APPROVED,
                    Akreditasi::STATUS_FINAL_REJECTED,
                    Akreditasi::STATUS_APPEAL_SUBMITTED,
                ],
                'count' => (int) ($counts[Akreditasi::STATUS_FINAL_APPROVED] ?? 0)
                    + (int) ($counts[Akreditasi::STATUS_FINAL_REJECTED] ?? 0)
                    + (int) ($counts[Akreditasi::STATUS_APPEAL_SUBMITTED] ?? 0),
            ],
            [
                'id' => 10,
                'label' => 'Selesai',
                'icon' => 'ki-double-check',
                'color' => 'success',
                'statusFilters' => [
                    Akreditasi::STATUS_COMPLETED,
                ],
                'count' => (int) ($counts[Akreditasi::STATUS_COMPLETED] ?? 0),
            ],
        ];
    }

    private function pipelineActiveIndex(array $pipelineSteps): ?int
    {
        foreach ($pipelineSteps as $i => $step) {
            if (($step['count'] ?? 0) > 0) {
                return $i;
            }
        }

        return null;
    }

    private function visitasiActions(Akreditasi $akreditasi): array
    {
        $actions = [
            ['label' => 'Detail', 'route' => route('superadmin.akreditasi.show', $akreditasi), 'color' => 'primary'],
        ];

        if (in_array($akreditasi->status, [Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW, Akreditasi::STATUS_VISITASI_SCHEDULED], true)) {
            $actions[] = [
                'label' => $akreditasi->status === Akreditasi::STATUS_VISITASI_SCHEDULED ? 'Perbarui Jadwal' : 'Jadwalkan Visitasi',
                'route' => route('superadmin.akreditasi.jadwalkan-visitasi', $akreditasi),
                'color' => 'info',
            ];
        }

        if ($akreditasi->status === Akreditasi::STATUS_POST_VISITASI_SCORING) {
            $actions[] = ['label' => 'Input NA1', 'route' => route('superadmin.akreditasi.input-na1', $akreditasi), 'color' => 'danger'];
            $actions[] = ['label' => 'Input NA2', 'route' => route('superadmin.akreditasi.input-na2', $akreditasi), 'color' => 'danger'];
            $actions[] = ['label' => 'Input NK', 'route' => route('superadmin.akreditasi.input-nk', $akreditasi), 'color' => 'danger'];
            $actions[] = ['label' => 'Upload Laporan', 'route' => route('superadmin.akreditasi.upload-laporan', $akreditasi), 'color' => 'primary'];
        }

        if (in_array($akreditasi->status, [Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED, Akreditasi::STATUS_ADMIN_FINAL_VALIDATION], true)) {
            $actions[] = ['label' => 'Validasi Akhir', 'route' => route('superadmin.akreditasi.validasi-akhir', $akreditasi), 'color' => 'success'];
        }

        return $actions;
    }
}

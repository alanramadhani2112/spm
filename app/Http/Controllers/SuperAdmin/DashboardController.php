<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
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

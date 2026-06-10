<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use Illuminate\Http\Request;
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

        return view('superadmin.dashboard.index', compact(
            'totalAkreditasi',
            'activeAkreditasi',
            'completedAkreditasi',
            'byStatus',
            'overdueCount',
            'period',
            'periodOptions',
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
            ->map(fn ($date) => \Illuminate\Support\Carbon::parse($date)->format('Y'))
            ->unique()
            ->mapWithKeys(fn ($year) => [(string) $year => (string) $year])
            ->all();

        return ['all' => 'Semua Periode'] + $years;
    }
}

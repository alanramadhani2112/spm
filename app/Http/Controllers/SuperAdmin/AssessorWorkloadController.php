<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\AssessorWorkloadService;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
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

    private function filterRows($rows, string $load)
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

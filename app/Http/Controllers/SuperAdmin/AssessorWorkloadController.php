<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\AssessorWorkloadService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessorWorkloadController extends Controller
{
    public function __construct(
        private AssessorWorkloadService $workloadService,
    ) {}

    public function index(Request $request): View
    {
        $period = $this->normalizePeriod((string) $request->query('period', 'all'));
        $load = $this->normalizeLoad((string) $request->query('load', 'all'));
        $rows = $this->workloadService->rows($period);
        $summary = $this->workloadService->summary($rows);
        $filteredRows = $load === 'all'
            ? $rows
            : $rows->where('level', $load)->values();
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

    private function normalizePeriod(string $period): string
    {
        return preg_match('/^\d{4}$/', $period) === 1 ? $period : 'all';
    }

    private function normalizeLoad(string $load): string
    {
        return in_array($load, ['normal', 'medium', 'high'], true) ? $load : 'all';
    }
}

<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AkreditasiAuditLog;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditController extends Controller
{
    public function __construct(
        private AuditTrailService $auditTrail,
    ) {}

    public function index(Request $request)
    {
        $query = $this->filteredQuery($request);

        $stats = [
            'total' => (clone $query)->count(),
            'action_types' => (clone $query)->whereNotNull('action_type')->distinct()->count('action_type'),
            'actors' => (clone $query)->whereNotNull('user_id')->distinct()->count('user_id'),
            'today' => (clone $query)->whereDate('created_at', now()->toDateString())->count(),
        ];
        $actionOptions = AkreditasiAuditLog::query()
            ->whereNotNull('action_type')
            ->distinct()
            ->orderBy('action_type')
            ->pluck('action_type')
            ->mapWithKeys(fn ($action) => [$action => AkreditasiAuditLog::getActionTypeLabel($action)])
            ->prepend('Semua', '')
            ->toArray();
        $hasFilters = $request->filled('actor') || $request->filled('action') || $request->filled('start_date') || $request->filled('end_date');
        $logs = $query->paginate(25)->withQueryString();

        return view('superadmin.audit.index', compact('logs', 'stats', 'actionOptions', 'hasFilters'));
    }

    public function export(Request $request)
    {
        $rows = $this->filteredQuery($request)
            ->with(['user', 'akreditasi'])
            ->limit(5000)
            ->get();

        $filters = $request->only(['actor', 'action', 'start_date', 'end_date']);

        $this->auditTrail->log('superadmin_exported', null, auth()->id(), [
            'export_type' => 'audit_trail',
            'format' => 'csv',
            'filters' => $filters,
            'rows_exported' => $rows->count(),
            'row_limit' => 5000,
        ]);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'ID',
                'Created At',
                'Actor ID',
                'Actor Name',
                'Action Type',
                'Action Label',
                'Akreditasi ID',
                'Akreditasi UUID',
                'From Status',
                'To Status',
                'Reason',
                'Metadata',
            ]);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->id,
                    $row->created_at?->toDateTimeString(),
                    $row->user_id,
                    $row->user?->name,
                    $row->action_type,
                    AkreditasiAuditLog::getActionTypeLabel((string) $row->action_type),
                    $row->akreditasi_id,
                    $row->akreditasi?->uuid,
                    $row->from_status,
                    $row->to_status,
                    $row->reason,
                    $row->metadata ? json_encode($row->metadata, JSON_UNESCAPED_SLASHES) : null,
                ]);
            }

            fclose($out);
        }, $this->auditExportFilename($filters), ['Content-Type' => 'text/csv']);
    }

    public function show(int $id)
    {
        $log = AkreditasiAuditLog::with(['user', 'akreditasi'])->findOrFail($id);

        return view('superadmin.audit.show', compact('log'));
    }

    private function filteredQuery(Request $request)
    {
        $query = AkreditasiAuditLog::query()->latest('created_at');

        if ($request->filled('actor')) {
            $query->where('user_id', $request->input('actor'));
        }

        if ($request->filled('action')) {
            $query->where('action_type', $request->input('action'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        return $query;
    }

    private function auditExportFilename(array $filters): string
    {
        $suffix = collect($filters)
            ->filter()
            ->map(fn ($value, $key) => Str::slug("{$key}-{$value}"))
            ->implode('-');

        return $suffix ? "audit-trail-{$suffix}.csv" : 'audit-trail.csv';
    }
}

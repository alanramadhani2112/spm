<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AkreditasiAuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AkreditasiAuditLog::with(['user', 'akreditasi'])->latest('created_at');

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

    public function show(int $id)
    {
        $log = AkreditasiAuditLog::with(['user', 'akreditasi'])->findOrFail($id);

        return view('superadmin.audit.show', compact('log'));
    }
}

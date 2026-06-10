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

        $logs = $query->paginate(25)->withQueryString();

        return view('superadmin.audit.index', compact('logs'));
    }

    public function show(int $id)
    {
        $log = AkreditasiAuditLog::with(['user', 'akreditasi'])->findOrFail($id);

        return view('superadmin.audit.show', compact('log'));
    }
}

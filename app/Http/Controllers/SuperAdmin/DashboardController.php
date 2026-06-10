<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAkreditasi = Akreditasi::count();
        $activeAkreditasi = Akreditasi::whereNotIn('status', Akreditasi::TERMINAL_STATUSES)->count();
        $completedAkreditasi = Akreditasi::whereIn('status', Akreditasi::TERMINAL_STATUSES)->count();

        $byStatus = Akreditasi::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->mapWithKeys(fn ($total, $status) => [$status => [
                'total' => $total,
                'label' => Akreditasi::STATUS_LABELS[$status] ?? $status,
            ]]);

        $overdueCount = Akreditasi::whereNotIn('status', Akreditasi::TERMINAL_STATUSES)
            ->whereNotNull('assessment_deadline')
            ->where('assessment_deadline', '<', now())
            ->count();

        return view('superadmin.dashboard.index', compact(
            'totalAkreditasi',
            'activeAkreditasi',
            'completedAkreditasi',
            'byStatus',
            'overdueCount',
        ));
    }
}

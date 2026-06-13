<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use App\Models\Banding;
use App\Models\FailedNotification;
use App\Models\Notification;
use App\Support\SuperAdminSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationCenterController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');
        $filter = in_array($filter, ['all', 'unread'], true) ? $filter : 'all';
        $userId = (int) auth()->id();

        $notificationQuery = Notification::query()
            ->with(['akreditasi.user.pesantren'])
            ->where('user_id', $userId)
            ->latest();

        if ($filter === 'unread') {
            $notificationQuery->where('is_read', false);
        }

        $notifications = $notificationQuery->paginate(15)->withQueryString();
        $unreadCount = Notification::where('user_id', $userId)->where('is_read', false)->count();
        $totalCount = Notification::where('user_id', $userId)->count();
        $failedCount = FailedNotification::count();
        $latestFailure = FailedNotification::latest()->first();
        $pendingBandingCount = Banding::where('status', 'pending')->count();
        $pendingSkCount = Akreditasi::where('status', Akreditasi::STATUS_FINAL_APPROVED)->count();
        $overdueQueues = $this->overdueQueues();

        return view('superadmin.notifications.index', compact(
            'notifications',
            'filter',
            'unreadCount',
            'totalCount',
            'failedCount',
            'latestFailure',
            'pendingBandingCount',
            'pendingSkCount',
            'overdueQueues',
        ));
    }

    public function markRead(Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        $notification->markAsRead();

        return redirect()->back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllRead(): RedirectResponse
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return redirect()->back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    private function overdueQueues(): array
    {
        $phaseMap = [
            'initial_review' => [
                'label' => 'Review Awal',
                'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
            ],
            'assessment_awal' => [
                'label' => 'Assessment',
                'status' => Akreditasi::STATUS_ASSESSMENT_OPEN,
            ],
            'admin_stage_1' => [
                'label' => 'Review Tahap 1',
                'status' => Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
            ],
            'stage_1_correction' => [
                'label' => 'Koreksi Tahap 1',
                'status' => Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION,
            ],
            'assessor_stage_2' => [
                'label' => 'Review Tahap 2',
                'status' => Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
            ],
            'stage_2_correction' => [
                'label' => 'Koreksi Tahap 2',
                'status' => Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION,
            ],
            'scoring' => [
                'label' => 'Scoring',
                'status' => Akreditasi::STATUS_POST_VISITASI_SCORING,
            ],
        ];

        return collect($phaseMap)
            ->map(function (array $phase, string $phaseKey) {
                $settingKey = SuperAdminSettings::deadlineKeyForPhase($phaseKey);
                $days = $settingKey ? SuperAdminSettings::int($settingKey) : null;
                $count = 0;

                if ($days !== null) {
                    $cutoff = now()->subDays($days);
                    $count = Akreditasi::where('status', $phase['status'])
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
                    'count' => $count,
                    'days' => $days,
                    'route' => route('superadmin.akreditasi.index', ['status' => $phase['status']]),
                ];
            })
            ->values()
            ->all();
    }
}

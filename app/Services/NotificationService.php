<?php

namespace App\Services;

use App\Models\Akreditasi;
use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    const EVENT_RECIPIENTS = [
        'initial_submitted' => ['admin', 'super_admin'],
        'assessment_awal_submitted' => ['admin'],
        'correction_requested' => ['pesantren'],
        'asesor_assigned' => ['asesor'],
        'visitasi_scheduled' => ['pesantren', 'admin'],
        'visitasi_result_submitted' => ['admin', 'super_admin'],
        'final_approved' => ['pesantren'],
        'final_rejected' => ['pesantren'],
        'appeal_submitted' => ['admin', 'super_admin'],
        'sk_terbit' => ['pesantren'],
    ];

    const EVENT_MESSAGES = [
        'initial_submitted' => 'Pengajuan awal akreditasi telah disubmit.',
        'assessment_awal_submitted' => 'Assessment awal telah disubmit untuk akreditasi.',
        'correction_requested' => 'Perbaikan diminta untuk akreditasi Anda.',
        'asesor_assigned' => 'Asesor telah ditugaskan untuk akreditasi.',
        'visitasi_scheduled' => 'Visitasi telah dijadwalkan.',
        'visitasi_result_submitted' => 'Hasil visitasi telah diserahkan oleh ketua asesor.',
        'final_approved' => 'Akreditasi Anda telah disetujui final.',
        'final_rejected' => 'Akreditasi Anda ditolak pada validasi akhir.',
        'appeal_submitted' => 'Banding telah diajukan untuk akreditasi.',
        'sk_terbit' => 'SK/Sertifikat telah terbit untuk akreditasi.',
    ];

    const ROLE_ID_MAP = [
        'admin' => 1,
        'asesor' => 2,
        'pesantren' => 3,
        'super_admin' => 4,
    ];

    public function send(int $userId, string $message, string $type, ?int $akreditasiId = null): void
    {
        Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'akreditasi_id' => $akreditasiId,
        ]);
    }

    public function notifyEvent(string $event, int $akreditasiId): void
    {
        $roles = self::EVENT_RECIPIENTS[$event] ?? [];

        if (empty($roles)) {
            return;
        }

        $akreditasi = Akreditasi::with('user')->findOrFail($akreditasiId);
        $message = $this->getEventMessage($event, $akreditasi);
        $sentUserIds = [];

        foreach ($roles as $role) {
            if ($role === 'pesantren') {
                if ($akreditasi->user && ! in_array($akreditasi->user->id, $sentUserIds)) {
                    $this->send($akreditasi->user->id, $message, $event, $akreditasiId);
                    $sentUserIds[] = $akreditasi->user->id;
                }

                continue;
            }

            $roleId = self::ROLE_ID_MAP[$role] ?? null;

            if ($roleId === null) {
                continue;
            }

            $users = User::where('role_id', $roleId)->get();

            foreach ($users as $user) {
                if (! in_array($user->id, $sentUserIds)) {
                    $this->send($user->id, $message, $event, $akreditasiId);
                    $sentUserIds[] = $user->id;
                }
            }
        }
    }

    private function getEventMessage(string $event, Akreditasi $akreditasi): string
    {
        return self::EVENT_MESSAGES[$event] ?? 'Notifikasi untuk akreditasi.';
    }
}

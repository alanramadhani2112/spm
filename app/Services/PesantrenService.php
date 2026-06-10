<?php

namespace App\Services;

use App\Models\Akreditasi;
use App\Models\Edpm;
use App\Models\Ipm;
use App\Models\Pesantren;
use App\Models\PesantrenUnit;
use App\Models\SdmPesantren;

class PesantrenService
{
    public const REQUIRED_PROFILE_FIELDS = [
        'nama_pesantren',
        'ns_pesantren',
        'alamat',
        'layanan_satuan_pendidikan',
        'provinsi_kode',
        'tahun_pendirian',
    ];

    public function checkDataCompleteness(int $userId): array
    {
        $pesantren = Pesantren::where('user_id', $userId)->first();

        if (! $pesantren) {
            return [
                'profilMinimum' => false,
                'assessmentReady' => false,
                'missingFields' => self::REQUIRED_PROFILE_FIELDS,
                'locked' => false,
            ];
        }

        $missingFields = [];

        foreach (self::REQUIRED_PROFILE_FIELDS as $field) {
            $value = $pesantren->{$field};

            if ($value === null || $value === '' || $value === []) {
                $missingFields[] = $field;
            }
        }

        $profilMinimum = empty($missingFields);

        $hasUnits = PesantrenUnit::where('pesantren_id', $pesantren->id)->exists();
        $hasIpm = Ipm::where('user_id', $userId)->exists();
        $hasEdpm = Edpm::where('user_id', $userId)->exists();
        $hasSdm = SdmPesantren::where('user_id', $userId)->exists();

        $assessmentReady = $profilMinimum && $hasUnits && $hasIpm && $hasEdpm && $hasSdm;

        return [
            'profilMinimum' => $profilMinimum,
            'assessmentReady' => $assessmentReady,
            'missingFields' => $missingFields,
            'locked' => (bool) $pesantren->is_locked,
        ];
    }

    public function isEligibleForSubmission(int $userId): bool
    {
        $completeness = $this->checkDataCompleteness($userId);

        if (! $completeness['profilMinimum']) {
            return false;
        }

        if ($completeness['locked']) {
            return false;
        }

        $hasActiveAkreditasi = Akreditasi::where('user_id', $userId)
            ->whereNotIn('status', Akreditasi::TERMINAL_STATUSES)
            ->exists();

        return ! $hasActiveAkreditasi;
    }

    public function lockProfile(int $pesantrenId): void
    {
        Pesantren::where('id', $pesantrenId)->update(['is_locked' => true]);
    }

    public function getRequiredProfileFields(): array
    {
        return self::REQUIRED_PROFILE_FIELDS;
    }
}

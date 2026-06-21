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

    public const IPM_BUTIRS = [
        'butir_1' => 'Pesantren memiliki legalitas (akta pendirian, izin operasional)',
        'butir_2' => 'Pesantren memiliki kurikulum pesantren secara tertulis',
        'butir_3' => 'Pesantren memiliki sistem penilaian santri secara tertulis',
        'butir_4' => 'Pesantren memiliki mudir/pimpinan yang definitif',
    ];

    public function checkDataCompleteness(int $userId): array
    {
        $pesantren = Pesantren::where('user_id', $userId)->first();

        if (! $pesantren) {
            return [
                'profilMinimum'   => false,
                'assessmentReady' => false,
                'missingFields'   => self::REQUIRED_PROFILE_FIELDS,
                'locked'          => false,
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

        $ipm     = Ipm::where('user_id', $userId)->first();
        $ipmData = $ipm?->data ?? [];
        $hasIpm  = $ipm !== null;

        // D.2: assessmentReady = IPM 4 butir semua "sesuai"
        $ipmAllSesuai = $hasIpm && count(array_filter(
            array_intersect_key($ipmData, self::IPM_BUTIRS),
            fn($v) => $v === 'sesuai'
        )) >= 4;

        $hasEdpm = Edpm::where('user_id', $userId)->exists();
        $hasSdm  = SdmPesantren::where('user_id', $userId)->exists();

        $assessmentReady = $profilMinimum && $hasUnits && $ipmAllSesuai && $hasEdpm && $hasSdm;

        return [
            'profilMinimum'   => $profilMinimum,
            'assessmentReady' => $assessmentReady,
            'missingFields'   => $missingFields,
            'locked'          => (bool) $pesantren->is_locked,
            'hasUnits'        => $hasUnits,
            'hasIpm'          => $hasIpm,
            'ipmAllSesuai'    => $ipmAllSesuai,
            'hasEdpm'         => $hasEdpm,
            'hasSdm'          => $hasSdm,
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

    public function unlockProfile(int $pesantrenId): void
    {
        Pesantren::where('id', $pesantrenId)->update(['is_locked' => false]);
    }

    public function getRequiredProfileFields(): array
    {
        return self::REQUIRED_PROFILE_FIELDS;
    }
}

<?php

namespace App\Services;

class ScoringService
{
    const KOMPONEN_CONFIG = [
        'MUTU_LULUSAN' => ['id' => 1, 'butir_count' => 8, 'bobot' => 35],
        'PROSES_PEMBELAJARAN' => ['id' => 2, 'butir_count' => 10, 'bobot' => 29],
        'MUTU_USTAZ' => ['id' => 3, 'butir_count' => 10, 'bobot' => 18],
        'MANAJEMEN_PESANTREN' => ['id' => 4, 'butir_count' => 12, 'bobot' => 18],
    ];

    const IPR_CONFIG = ['id' => 5, 'butir_count' => 22];

    const SKALA_NILAI = [1, 2, 3, 4];

    const TOTAL_BOBOT = 100;

    const RANK_A_THRESHOLD = 86;
    const RANK_B_THRESHOLD = 71;

    const IK_WEIGHT = 0.7;
    const IPR_WEIGHT = 0.3;

    const MAX_SCORE = 100;
    const MIN_SCORE = 0;
    const SCALE_MULTIPLIER = 25;

    public function calculateIK(array $butirScores): float
    {
        $komponenScores = $this->groupByKomponen($butirScores);
        $weightedSum = 0;
        $totalBobot = 0;

        foreach (self::KOMPONEN_CONFIG as $komponen) {
            $komponenId = $komponen['id'];
            $bobot = $komponen['bobot'];

            if (!empty($komponenScores[$komponenId])) {
                $avg = array_sum($komponenScores[$komponenId]) / count($komponenScores[$komponenId]);
            } else {
                $avg = 0;
            }

            $weightedSum += $avg * $bobot;
            $totalBobot += $bobot;
        }

        return $totalBobot > 0 ? $weightedSum / $totalBobot : 0;
    }

    public function calculateIPR(array $iprScores): float
    {
        $validScores = array_values(array_filter($iprScores, function ($score) {
            return in_array($score, self::SKALA_NILAI);
        }));

        if (count($validScores) === 0) {
            return 0;
        }

        return array_sum($validScores) / count($validScores);
    }

    public function calculateNA(float $ik, float $ipr): float
    {
        return (self::IK_WEIGHT * $ik + self::IPR_WEIGHT * $ipr) * self::SCALE_MULTIPLIER;
    }

    public function calculateFinalScore(float $na): float
    {
        return max(self::MIN_SCORE, min(self::MAX_SCORE, round($na)));
    }

    public function calculateFinalScoreFromAverage(float $average): float
    {
        return round(max(self::MIN_SCORE, min(self::MAX_SCORE, $average * self::SCALE_MULTIPLIER)), 2);
    }

    public function calculatePeringkatFromAverage(float $average): string
    {
        return $this->calculatePeringkat($this->calculateFinalScoreFromAverage($average));
    }

    public function calculatePeringkat(float $finalScore): string
    {
        if ($finalScore >= self::RANK_A_THRESHOLD) {
            return 'A';
        }
        if ($finalScore >= self::RANK_B_THRESHOLD) {
            return 'B';
        }
        return 'C';
    }

    public function calculateNkByKomponen(array $butirScores): array
    {
        $komponenScores = $this->groupByKomponen($butirScores);
        $nkScores = [];

        foreach (self::KOMPONEN_CONFIG as $key => $komponen) {
            $komponenId = $komponen['id'];
            if (!empty($komponenScores[$komponenId])) {
                $nkScores[$key] = array_sum($komponenScores[$komponenId]) / count($komponenScores[$komponenId]);
            } else {
                $nkScores[$key] = 0;
            }
        }

        return $nkScores;
    }

    public function calculateNvDefault(array $nkScores): array
    {
        return $nkScores;
    }

    private function groupByKomponen(array $butirScores): array
    {
        $grouped = [];

        foreach ($butirScores as $score) {
            $komponenId = $score['komponen_id'] ?? null;
            $isian = $score['isian'] ?? null;

            if ($komponenId === null || $isian === null) {
                continue;
            }

            if (!isset($grouped[$komponenId])) {
                $grouped[$komponenId] = [];
            }

            $grouped[$komponenId][] = (float) $isian;
        }

        return $grouped;
    }

    public static function getPeringkatLabel(string $peringkat): string
    {
        return match ($peringkat) {
            'A' => 'Unggul',
            'B' => 'Baik',
            'C' => 'Cukup',
            default => 'Tidak Terakreditasi',
        };
    }

    public function calculateAll(array $butirScores, array $iprScores): array
    {
        $ik = $this->calculateIK($butirScores);
        $ipr = $this->calculateIPR($iprScores);
        $na = $this->calculateNA($ik, $ipr);
        $finalScore = $this->calculateFinalScore($na);
        $peringkat = $this->calculatePeringkat($finalScore);
        $nk = $this->calculateNkByKomponen($butirScores);
        $nv = $this->calculateNvDefault($nk);

        return [
            'ik' => round($ik, 6),
            'ipr' => round($ipr, 6),
            'na' => round($na, 6),
            'final_score' => $finalScore,
            'peringkat' => $peringkat,
            'peringkat_label' => self::getPeringkatLabel($peringkat),
            'nk' => array_map(fn($v) => round($v, 6), $nk),
            'nv' => array_map(fn($v) => round($v, 6), $nv),
        ];
    }
}

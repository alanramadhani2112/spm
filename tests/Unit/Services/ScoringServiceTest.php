<?php

namespace Tests\Unit\Services;

use App\Services\ScoringService;
use Tests\TestCase;

class ScoringServiceTest extends TestCase
{
    private ScoringService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ScoringService();
    }

    private function makeButirScores(int $k1, int $k2, int $k3, int $k4): array
    {
        $scores = [];

        foreach ([1 => [8, $k1], 2 => [10, $k2], 3 => [10, $k3], 4 => [12, $k4]] as $komponenId => [$count, $isian]) {
            for ($i = 0; $i < $count; $i++) {
                $scores[] = ['komponen_id' => $komponenId, 'isian' => $isian];
            }
        }

        return $scores;
    }

    public function test_calculate_ik_all_max_scores(): void
    {
        $butirScores = $this->makeButirScores(4, 4, 4, 4);

        $this->assertEqualsWithDelta(4.0, $this->service->calculateIK($butirScores), 0.0001);
    }

    public function test_calculate_ik_mixed_scores(): void
    {
        $butirScores = $this->makeButirScores(3, 3, 4, 3);

        $this->assertEqualsWithDelta(3.18, $this->service->calculateIK($butirScores), 0.0001);
    }

    public function test_calculate_ipr_all_same(): void
    {
        $iprScores = array_fill(0, 22, 3);

        $this->assertEqualsWithDelta(3.0, $this->service->calculateIPR($iprScores), 0.0001);
    }

    public function test_calculate_ipr_mixed(): void
    {
        $iprScores = array_merge(array_fill(0, 11, 4), array_fill(0, 11, 2));

        $this->assertEqualsWithDelta(3.0, $this->service->calculateIPR($iprScores), 0.0001);
    }

    public function test_calculate_na(): void
    {
        $this->assertEqualsWithDelta(100.0, $this->service->calculateNA(4.0, 4.0), 0.0001);
        $this->assertEqualsWithDelta(78.15, $this->service->calculateNA(3.18, 3.0), 0.0001);
    }

    public function test_calculate_final_score_rounds_correctly(): void
    {
        $this->assertEquals(78, $this->service->calculateFinalScore(78.15));
        $this->assertEquals(79, $this->service->calculateFinalScore(78.5));
        $this->assertEquals(100, $this->service->calculateFinalScore(100));
    }

    public function test_calculate_peringkat(): void
    {
        $this->assertEquals('A', $this->service->calculatePeringkat(100));
        $this->assertEquals('A', $this->service->calculatePeringkat(86));
        $this->assertEquals('B', $this->service->calculatePeringkat(85));
        $this->assertEquals('B', $this->service->calculatePeringkat(71));
        $this->assertEquals('C', $this->service->calculatePeringkat(70));
        $this->assertEquals('C', $this->service->calculatePeringkat(53));
    }

    public function test_calculate_all_high(): void
    {
        $butirScores = $this->makeButirScores(4, 4, 4, 4);
        $iprScores = array_fill(0, 22, 4);

        $result = $this->service->calculateAll($butirScores, $iprScores);

        $this->assertEquals(100, $result['final_score']);
        $this->assertEquals('A', $result['peringkat']);
    }

    public function test_calculate_all_minimum(): void
    {
        $butirScores = $this->makeButirScores(1, 1, 1, 1);
        $iprScores = array_fill(0, 22, 1);

        $result = $this->service->calculateAll($butirScores, $iprScores);

        $this->assertEqualsWithDelta(1.0, $result['ik'], 0.0001);
        $this->assertEqualsWithDelta(1.0, $result['ipr'], 0.0001);
        $this->assertEqualsWithDelta(25.0, $result['na'], 0.0001);
        $this->assertEquals(25, $result['final_score']);
        $this->assertEquals('C', $result['peringkat']);
    }

    public function test_calculate_nk_by_komponen(): void
    {
        $butirScores = $this->makeButirScores(3, 3, 4, 3);

        $nk = $this->service->calculateNkByKomponen($butirScores);

        $this->assertEqualsWithDelta(3.0, $nk['MUTU_LULUSAN'], 0.0001);
        $this->assertEqualsWithDelta(3.0, $nk['PROSES_PEMBELAJARAN'], 0.0001);
        $this->assertEqualsWithDelta(4.0, $nk['MUTU_USTAZ'], 0.0001);
        $this->assertEqualsWithDelta(3.0, $nk['MANAJEMEN_PESANTREN'], 0.0001);
    }

    public function test_calculate_nv_default_mirrors_nk(): void
    {
        $butirScores = $this->makeButirScores(2, 3, 4, 1);

        $nk = $this->service->calculateNkByKomponen($butirScores);
        $nv = $this->service->calculateNvDefault($nk);

        $this->assertSame($nk, $nv);
    }
}

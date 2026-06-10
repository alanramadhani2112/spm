<?php

namespace Tests\Feature;

use App\Services\ScoringService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GoldenSampleTest extends TestCase
{
    private ScoringService $scoringService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scoringService = new ScoringService();
    }

    #[DataProvider('goldenSampleProvider')]
    public function test_golden_sample_scoring(string $name, array $butirScores, array $iprScores, array $expected): void
    {
        $result = $this->scoringService->calculateAll($butirScores, $iprScores);

        $this->assertEquals($expected['final_score'], $result['final_score'], "Final score mismatch for sample: {$name}");
        $this->assertEquals($expected['peringkat'], $result['peringkat'], "Peringkat mismatch for sample: {$name}");
        $this->assertEqualsWithDelta($expected['ik'], $result['ik'], 0.001, "IK mismatch for sample: {$name}");
        $this->assertEqualsWithDelta($expected['ipr'], $result['ipr'], 0.001, "IPR mismatch for sample: {$name}");
        $this->assertEqualsWithDelta($expected['na'], $result['na'], 0.01, "NA mismatch for sample: {$name}");
    }

    public static function goldenSampleProvider(): array
    {
        $samples = json_decode(file_get_contents(__DIR__ . '/../Fixtures/golden-sample.json'), true);
        $data = [];
        foreach ($samples as $sample) {
            $data[$sample['name']] = [$sample['name'], $sample['butir_scores'], $sample['ipr_scores'], $sample['expected']];
        }
        return $data;
    }
}

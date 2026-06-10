<?php

namespace Database\Seeders;

use App\Models\MasterEdpmButir;
use App\Models\MasterEdpmKomponen;
use App\Services\ScoringService;
use Illuminate\Database\Seeder;

class MasterEdpmSeeder extends Seeder
{
    public function run(): void
    {
        $ikKomponen = [];
        foreach (ScoringService::KOMPONEN_CONFIG as $key => $config) {
            $name = str_replace('_', ' ', $key);
            $name = ucwords(strtolower($name));

            $komponen = MasterEdpmKomponen::firstOrCreate(
                ['id' => $config['id']],
                ['kode' => $key, 'name' => $name, 'nama' => $name]
            );

            for ($i = 1; $i <= $config['butir_count']; $i++) {
                $butirName = 'Butir ' . $i;

                MasterEdpmButir::firstOrCreate(
                    [
                        'komponen_id' => $komponen->id,
                        'name' => $butirName,
                    ],
                    [
                        'kode' => "{$config['id']}.{$i}",
                        'nama' => $butirName,
                    ]
                );
            }
        }

        $iprKomponen = MasterEdpmKomponen::firstOrCreate(
            ['id' => ScoringService::IPR_CONFIG['id']],
            ['kode' => 'IPR', 'name' => 'IPR', 'nama' => 'IPR']
        );

        for ($i = 1; $i <= ScoringService::IPR_CONFIG['butir_count']; $i++) {
            $butirName = 'Butir ' . $i;

            MasterEdpmButir::firstOrCreate(
                [
                    'komponen_id' => $iprKomponen->id,
                    'name' => $butirName,
                ],
                [
                    'kode' => "IPR.{$i}",
                    'nama' => $butirName,
                ]
            );
        }
    }
}

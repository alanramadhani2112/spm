<?php

namespace Database\Seeders;

use App\Models\SuperAdminSetting;
use App\Support\SuperAdminSettings;
use Illuminate\Database\Seeder;

class SuperAdminSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SuperAdminSettings::DEFAULTS as $key => $value) {
            SuperAdminSetting::firstOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}

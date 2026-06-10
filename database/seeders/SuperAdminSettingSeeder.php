<?php

namespace Database\Seeders;

use App\Models\SuperAdminSetting;
use Illuminate\Database\Seeder;

class SuperAdminSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'review_awal_deadline', 'value' => 5],
            ['key' => 'assessment_deadline', 'value' => 14],
            ['key' => 'review_tahap1_deadline', 'value' => 5],
            ['key' => 'correction_tahap1_deadline', 'value' => 7],
            ['key' => 'max_siklus_tahap1', 'value' => 2],
            ['key' => 'review_tahap2_deadline', 'value' => 5],
            ['key' => 'correction_tahap2_deadline', 'value' => 7],
            ['key' => 'max_siklus_tahap2', 'value' => 2],
            ['key' => 'scoring_deadline', 'value' => 7],
            ['key' => 'kartu_kendali_wajib', 'value' => 'before_admin_validation'],
            ['key' => 'laporan_wajib', 'value' => 'before_submit'],
            ['key' => 'nv_override_allowed', 'value' => 'true'],
            ['key' => 'nv_reason_mode', 'value' => 'collective'],
            ['key' => 'superadmin_receives_admin_notif', 'value' => 'true'],
            ['key' => 'banding_deadline', 'value' => 7],
            ['key' => 'reminder_days', 'value' => 2],
            ['key' => 'action_on_limit', 'value' => 'manual_review'],
        ];

        foreach ($settings as $setting) {
            SuperAdminSetting::firstOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}

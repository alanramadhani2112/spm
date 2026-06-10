<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdminSetting;
use App\Services\AuditTrailService;
use Exception;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private const SETTING_CATEGORIES = [
        'deadline' => [
            'review_awal_deadline', 'assessment_deadline', 'review_tahap1_deadline',
            'correction_tahap1_deadline', 'review_tahap2_deadline', 'correction_tahap2_deadline',
            'scoring_deadline', 'banding_deadline',
        ],
        'correction' => [
            'max_siklus_tahap1', 'max_siklus_tahap2', 'action_on_limit',
        ],
        'document' => [
            'kartu_kendali_wajib_before', 'laporan_wajib_before',
        ],
        'nv' => [
            'nv_override_allowed', 'nv_reason_mode',
        ],
        'notification' => [
            'superadmin_receives_admin_notif', 'reminder_days',
        ],
        'banding' => [
            'banding_eligibility',
        ],
    ];

    private const SETTING_DEFAULTS = [
        'review_awal_deadline' => 5,
        'assessment_deadline' => 14,
        'review_tahap1_deadline' => 5,
        'correction_tahap1_deadline' => 7,
        'max_siklus_tahap1' => 2,
        'review_tahap2_deadline' => 5,
        'correction_tahap2_deadline' => 7,
        'max_siklus_tahap2' => 2,
        'scoring_deadline' => 7,
        'banding_deadline' => 7,
        'kartu_kendali_wajib_before' => 'before_admin_validation',
        'laporan_wajib_before' => 'before_submit',
        'nv_override_allowed' => true,
        'nv_reason_mode' => 'collective',
        'superadmin_receives_admin_notif' => true,
        'reminder_days' => 7,
        'action_on_limit' => 'reject',
        'banding_eligibility' => 'all',
    ];

    public function __construct(
        private AuditTrailService $auditTrail,
    ) {}

    public function index()
    {
        $settings = SuperAdminSetting::all()->keyBy('key')->map->value;
        $categories = [];

        foreach (self::SETTING_CATEGORIES as $category => $keys) {
            $categories[$category] = [];
            foreach ($keys as $key) {
                $categories[$category][$key] = $settings->get($key, self::SETTING_DEFAULTS[$key] ?? null);
            }
        }

        return view('superadmin.settings.index', compact('categories'));
    }

    public function update(Request $request)
    {
        $request->merge(array_map('trim', $request->all()));

        $validated = $request->validate([
            'key' => 'required|string|in:' . implode(',', $this->allSettingKeys()),
            'value' => 'required',
            'reason' => 'required|string|min:3',
        ]);

        try {
            $key = $validated['key'];
            $newValue = $validated['value'];

            $setting = SuperAdminSetting::where('key', $key)->first();
            $oldValue = $setting?->value;

            $setting = SuperAdminSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $newValue,
                    'description' => self::SETTING_DEFAULTS[$key] ?? null,
                    'updated_by' => auth()->id(),
                ]
            );

            $this->auditTrail->log('setting_changed', 0, auth()->id(), [
                'setting_key' => $key,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'reason' => $validated['reason'],
            ]);

            session()->flash('success', "Setting '{$key}' berhasil diperbarui.");
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->back();
    }

    public function deadline()
    {
        $settings = $this->loadCategory('deadline');

        return view('superadmin.settings.deadline', compact('settings'));
    }

    public function correction()
    {
        $settings = $this->loadCategory('correction');

        return view('superadmin.settings.correction', compact('settings'));
    }

    public function dokumen()
    {
        $settings = $this->loadCategory('document');

        return view('superadmin.settings.dokumen', compact('settings'));
    }

    public function nv()
    {
        $settings = $this->loadCategory('nv');

        return view('superadmin.settings.nv', compact('settings'));
    }

    public function notifikasi()
    {
        $settings = $this->loadCategory('notification');

        return view('superadmin.settings.notifikasi', compact('settings'));
    }

    public function banding()
    {
        $settings = $this->loadCategory('banding');

        return view('superadmin.settings.banding', compact('settings'));
    }

    private function loadCategory(string $category): array
    {
        $dbSettings = SuperAdminSetting::all()->keyBy('key')->map->value;
        $settings = [];

        foreach (self::SETTING_CATEGORIES[$category] as $key) {
            $settings[$key] = $dbSettings->get($key, self::SETTING_DEFAULTS[$key] ?? null);
        }

        return $settings;
    }

    private function allSettingKeys(): array
    {
        return array_merge(...array_values(self::SETTING_CATEGORIES));
    }
}

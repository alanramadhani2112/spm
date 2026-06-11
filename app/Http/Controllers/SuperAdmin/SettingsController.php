<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdminSetting;
use App\Services\AuditTrailService;
use App\Support\SuperAdminSettings;
use Exception;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        private AuditTrailService $auditTrail,
    ) {}

    public function index()
    {
        $settings = SuperAdminSetting::all()->keyBy('key')->map->value;
        $categories = [];

        foreach (SuperAdminSettings::CATEGORIES as $category => $keys) {
            $categories[$category] = [];
            foreach ($keys as $key) {
                $categories[$category][$key] = $settings->get($key, SuperAdminSettings::default($key));
            }
        }

        return view('superadmin.settings.index', compact('categories'));
    }

    public function update(Request $request)
    {
        $request->merge(array_map('trim', $request->all()));

        $validated = $request->validate([
            'key' => 'required|string|in:'.implode(',', $this->allSettingKeys()),
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
                    'description' => SuperAdminSettings::default($key),
                    'updated_by' => auth()->id(),
                ]
            );

            SuperAdminSettings::forget($setting->key);

            $this->auditTrail->log('setting_changed', null, auth()->id(), [
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

        foreach (SuperAdminSettings::keysForCategory($category) as $key) {
            $settings[$key] = $dbSettings->get($key, SuperAdminSettings::default($key));
        }

        return $settings;
    }

    private function allSettingKeys(): array
    {
        return SuperAdminSettings::allKeys();
    }
}

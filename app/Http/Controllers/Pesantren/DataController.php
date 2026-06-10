<?php

namespace App\Http\Controllers\Pesantren;

use App\Http\Controllers\Controller;
use App\Models\Edpm;
use App\Models\Ipm;
use App\Models\Pesantren;
use App\Models\PesantrenUnit;
use App\Models\SdmPesantren;
use App\Services\PesantrenService;
use Illuminate\Http\Request;

class DataController extends Controller
{
    private const DOCUMENT_FIELDS = [
        'dok_profil',
        'dok_nsp',
        'dok_renstra',
        'dok_rk_anggaran',
        'dok_kurikulum',
        'dok_silabus_rpp',
        'dok_kepengasuhan',
        'dok_peraturan_kepegawaian',
        'dok_sarpras',
        'dok_laporan_tahunan',
        'dok_sop',
    ];

    public function __construct(
        private readonly PesantrenService $pesantrenService,
    ) {}

    public function index()
    {
        return view('pesantren.data.index', $this->viewData());
    }

    public function updateProfile(Request $request)
    {
        $pesantren = Pesantren::firstOrNew(['user_id' => auth()->id()]);

        if ($pesantren->exists && $pesantren->is_locked) {
            return back()->with('error', 'Profil pesantren sudah dikunci setelah pengajuan dikirim.');
        }

        $validated = $request->validate([
            'nama_pesantren' => ['required', 'string', 'max:255'],
            'ns_pesantren' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string'],
            'provinsi_kode' => ['required', 'string', 'max:20'],
            'tahun_pendirian' => ['required', 'string', 'max:10'],
            'kota_kabupaten' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kelurahan' => ['nullable', 'string', 'max:255'],
            'nama_mudir' => ['nullable', 'string', 'max:255'],
            'jenjang_pendidikan_mudir' => ['nullable', 'string', 'max:255'],
            'telp_pesantren' => ['nullable', 'string', 'max:50'],
            'hp_wa' => ['nullable', 'string', 'max:50'],
            'email_pesantren' => ['nullable', 'email', 'max:255'],
            'persyarikatan' => ['nullable', 'string', 'max:255'],
            'visi' => ['nullable', 'string'],
            'misi' => ['nullable', 'string'],
            'luas_tanah' => ['nullable', 'string', 'max:100'],
            'luas_bangunan' => ['nullable', 'string', 'max:100'],
            'layanan_satuan_pendidikan' => ['required', 'array', 'min:1'],
            'layanan_satuan_pendidikan.*' => ['string', 'max:100'],
            'units' => ['required', 'array', 'min:1'],
            'units.*.layanan_satuan_pendidikan' => ['nullable', 'string', 'max:100'],
            'units.*.jumlah_rombel' => ['nullable', 'integer', 'min:0'],
        ] + $this->documentRules());

        foreach (self::DOCUMENT_FIELDS as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('pesantren-documents');
            } else {
                unset($validated[$field]);
            }
        }

        $units = collect($validated['units'] ?? [])
            ->filter(fn (array $unit) => ! empty($unit['layanan_satuan_pendidikan']))
            ->values();

        unset($validated['units']);

        $pesantren->fill($validated + ['user_id' => auth()->id()]);
        $pesantren->save();

        PesantrenUnit::where('pesantren_id', $pesantren->id)->delete();
        foreach ($units as $unit) {
            PesantrenUnit::create([
                'pesantren_id' => $pesantren->id,
                'layanan_satuan_pendidikan' => $unit['layanan_satuan_pendidikan'],
                'jumlah_rombel' => (int) ($unit['jumlah_rombel'] ?? 0),
            ]);
        }

        return redirect()->route('pesantren.data.index')->with('success', 'Profil dan unit pesantren berhasil disimpan.');
    }

    public function updateIpm(Request $request)
    {
        $validated = $request->validate([
            'ipm.santri_mukim' => ['required', 'integer', 'min:0'],
            'ipm.santri_non_mukim' => ['nullable', 'integer', 'min:0'],
            'ipm.jumlah_rombongan_belajar' => ['nullable', 'integer', 'min:0'],
            'ipm.kurikulum_utama' => ['nullable', 'string', 'max:255'],
            'ipm.catatan_mutu' => ['nullable', 'string'],
        ]);

        Ipm::updateOrCreate(
            ['user_id' => auth()->id()],
            ['data' => $validated['ipm']]
        );

        return redirect()->route('pesantren.data.index')->with('success', 'Data IPM berhasil disimpan.');
    }

    public function updateSdm(Request $request)
    {
        $validated = $request->validate([
            'sdm.ustaz_tetap' => ['required', 'integer', 'min:0'],
            'sdm.ustaz_tidak_tetap' => ['nullable', 'integer', 'min:0'],
            'sdm.tenaga_kependidikan' => ['nullable', 'integer', 'min:0'],
            'sdm.rasio_pengasuh_santri' => ['nullable', 'string', 'max:100'],
            'sdm.catatan_sdm' => ['nullable', 'string'],
        ]);

        SdmPesantren::updateOrCreate(
            ['user_id' => auth()->id()],
            ['data' => $validated['sdm']]
        );

        return redirect()->route('pesantren.data.index')->with('success', 'Data SDM berhasil disimpan.');
    }

    public function updateEdpm(Request $request)
    {
        $validated = $request->validate([
            'edpm.self_assessment' => ['required', 'string'],
            'edpm.kesiapan_dokumen' => ['nullable', 'string', 'max:255'],
            'edpm.catatan_ipr' => ['nullable', 'string'],
        ]);

        Edpm::updateOrCreate(
            ['user_id' => auth()->id()],
            ['data' => $validated['edpm']]
        );

        return redirect()->route('pesantren.data.index')->with('success', 'Data EDPM/IPR berhasil disimpan.');
    }

    private function viewData(): array
    {
        $userId = auth()->id();

        return [
            'pesantren' => Pesantren::with('units')->where('user_id', $userId)->first(),
            'ipm' => Ipm::where('user_id', $userId)->first(),
            'sdm' => SdmPesantren::where('user_id', $userId)->first(),
            'edpm' => Edpm::where('user_id', $userId)->first(),
            'completeness' => $this->pesantrenService->checkDataCompleteness($userId),
        ];
    }

    private function documentRules(): array
    {
        $rules = [];

        foreach (self::DOCUMENT_FIELDS as $field) {
            $rules[$field] = ['nullable', 'file', 'mimes:pdf', 'max:5120'];
        }

        return $rules;
    }
}

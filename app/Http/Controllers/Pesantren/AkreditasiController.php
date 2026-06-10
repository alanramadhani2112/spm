<?php

namespace App\Http\Controllers\Pesantren;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use App\Models\Edpm;
use App\Models\Ipm;
use App\Models\Pesantren;
use App\Models\SdmPesantren;
use App\Services\AkreditasiWorkflowService;
use App\Services\PesantrenService;
use Exception;
use Illuminate\Http\Request;

class AkreditasiController extends Controller
{
    public function index()
    {
        $akreditasis = Akreditasi::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pesantren.akreditasi.index', compact('akreditasis'));
    }

    public function pengajuanForm()
    {
        $completeness = app(PesantrenService::class)->checkDataCompleteness(auth()->id());

        return view('pesantren.akreditasi.pengajuan', compact('completeness'));
    }

    public function submitPengajuan(Request $request)
    {
        try {
            app(AkreditasiWorkflowService::class)->submitPengajuanAwal(auth()->id());

            session()->flash('success', 'Pengajuan akreditasi berhasil dikirim.');
            return redirect()->route('pesantren.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function assessmentForm($akreditasiId)
    {
        $akreditasi = Akreditasi::where('user_id', auth()->id())->findOrFail($akreditasiId);
        $data = $this->pesantrenData(auth()->id());

        return view('pesantren.akreditasi.assessment', compact('akreditasi') + $data);
    }

    public function submitAssessment(Request $request, $akreditasiId)
    {
        $validated = $request->validate($this->assessmentRules(required: true));

        try {
            Akreditasi::where('user_id', auth()->id())->findOrFail($akreditasiId);
            $payload = $this->storeAssessmentPayload($request, auth()->id(), $validated);

            app(AkreditasiWorkflowService::class)->pesantrenSubmitAssessment(
                $akreditasiId,
                $payload
            );

            session()->flash('success', 'Assessment berhasil dikirim.');
            return redirect()->route('pesantren.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function correctionForm($akreditasiId)
    {
        $akreditasi = Akreditasi::where('user_id', auth()->id())->findOrFail($akreditasiId);
        $data = $this->pesantrenData(auth()->id());

        return view('pesantren.akreditasi.koreksi', compact('akreditasi') + $data);
    }

    public function submitCorrection(Request $request, $akreditasiId)
    {
        $validated = $request->validate($this->assessmentRules(required: false));

        try {
            $akreditasi = Akreditasi::where('user_id', auth()->id())->findOrFail($akreditasiId);

            $correctionData = $this->storeAssessmentPayload($request, auth()->id(), $validated);

            if ($akreditasi->status === Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION) {
                app(AkreditasiWorkflowService::class)->pesantrenSubmitStage1Correction($akreditasiId, $correctionData);
            } elseif ($akreditasi->status === Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION) {
                app(AkreditasiWorkflowService::class)->pesantrenSubmitStage2Correction(
                    $akreditasiId,
                    $correctionData
                );
            } else {
                session()->flash('error', 'Status akreditasi tidak memungkinkan pengiriman koreksi.');
                return redirect()->back();
            }

            session()->flash('success', 'Koreksi berhasil dikirim.');
            return redirect()->route('pesantren.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function uploadKartuKendali(Request $request, $akreditasiId)
    {
        try {
            $akreditasi = Akreditasi::where('user_id', auth()->id())->findOrFail($akreditasiId);

            app(AkreditasiWorkflowService::class)->pesantrenUploadKartuKendali(
                $akreditasiId,
                auth()->id(),
                $request->file('file')
            );

            session()->flash('success', 'Kartu kendali berhasil diupload.');
            return redirect()->route('pesantren.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function hasilAkhir($akreditasiId)
    {
        $akreditasi = Akreditasi::where('user_id', auth()->id())->findOrFail($akreditasiId);

        return view('pesantren.akreditasi.hasil', compact('akreditasi'));
    }

    public function submitBanding(Request $request, $akreditasiId)
    {
        $request->validate([
            'alasan' => 'required|string',
        ]);

        try {
            $akreditasi = Akreditasi::where('user_id', auth()->id())->findOrFail($akreditasiId);

            app(AkreditasiWorkflowService::class)->pesantrenSubmitBanding(
                $akreditasiId,
                auth()->id(),
                $request->input('alasan')
            );

            session()->flash('success', 'Banding berhasil diajukan.');
            return redirect()->route('pesantren.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    private function pesantrenData(int $userId): array
    {
        return [
            'pesantren' => Pesantren::with('units')->where('user_id', $userId)->first(),
            'ipm' => Ipm::where('user_id', $userId)->first(),
            'sdm' => SdmPesantren::where('user_id', $userId)->first(),
            'edpm' => Edpm::where('user_id', $userId)->first(),
        ];
    }

    private function assessmentRules(bool $required): array
    {
        $requiredRule = $required ? 'required' : 'nullable';

        return [
            'ipm.santri_mukim' => [$requiredRule, 'integer', 'min:0'],
            'ipm.santri_non_mukim' => ['nullable', 'integer', 'min:0'],
            'ipm.jumlah_rombongan_belajar' => ['nullable', 'integer', 'min:0'],
            'ipm.kurikulum_utama' => ['nullable', 'string', 'max:255'],
            'ipm.catatan_mutu' => ['nullable', 'string'],
            'sdm.ustaz_tetap' => [$requiredRule, 'integer', 'min:0'],
            'sdm.ustaz_tidak_tetap' => ['nullable', 'integer', 'min:0'],
            'sdm.tenaga_kependidikan' => ['nullable', 'integer', 'min:0'],
            'sdm.rasio_pengasuh_santri' => ['nullable', 'string', 'max:100'],
            'sdm.catatan_sdm' => ['nullable', 'string'],
            'edpm.self_assessment' => [$requiredRule, 'string'],
            'edpm.kesiapan_dokumen' => ['nullable', 'string', 'max:255'],
            'edpm.catatan_ipr' => ['nullable', 'string'],
            'dok_profil' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'dok_nsp' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'dok_renstra' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'dok_rk_anggaran' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'dok_kurikulum' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'dok_silabus_rpp' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'dok_kepengasuhan' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'dok_peraturan_kepegawaian' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'dok_sarpras' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'dok_laporan_tahunan' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'dok_sop' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ];
    }

    private function storeAssessmentPayload(Request $request, int $userId, array $validated): array
    {
        $payload = [];

        if (isset($validated['ipm'])) {
            Ipm::updateOrCreate(['user_id' => $userId], ['data' => $validated['ipm']]);
            $payload['ipm'] = $validated['ipm'];
        }

        if (isset($validated['sdm'])) {
            SdmPesantren::updateOrCreate(['user_id' => $userId], ['data' => $validated['sdm']]);
            $payload['sdm'] = $validated['sdm'];
        }

        if (isset($validated['edpm'])) {
            Edpm::updateOrCreate(['user_id' => $userId], ['data' => $validated['edpm']]);
            $payload['edpm'] = $validated['edpm'];
        }

        $pesantren = Pesantren::where('user_id', $userId)->first();
        if ($pesantren) {
            foreach (array_keys($this->assessmentRules(required: false)) as $field) {
                if (! str_starts_with($field, 'dok_') || ! $request->hasFile($field)) {
                    continue;
                }

                $pesantren->{$field} = $request->file($field)->store('pesantren-documents');
                $payload['documents'][$field] = $pesantren->{$field};
            }
            $pesantren->save();
        }

        return $payload;
    }
}


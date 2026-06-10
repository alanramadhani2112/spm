<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use App\Models\AkreditasiEdpm;
use App\Models\Assessment;
use App\Models\Banding;
use App\Models\User;
use App\Services\AkreditasiWorkflowService;
use App\Services\BandingService;
use App\Services\ScoringService;
use Exception;
use Illuminate\Http\Request;

/**
 * SuperAdmin Akreditasi Controller
 *
 * Superadmin has god-mode access to ALL operational features:
 * admin actions + asesor actions + pesantren actions.
 * No user_id or asesor_id filters — sees everything.
 *
 * Reuses existing views from admin/asesor/pesantren.
 */
class AkreditasiController extends Controller
{
    public function __construct(
        private AkreditasiWorkflowService $workflowService,
        private BandingService $bandingService,
        private ScoringService $scoringService,
    ) {}

    // ============================================================
    // INDEX — lihat semua akreditasi (termasuk terminal)
    // ============================================================

    public function index()
    {
        $akreditasis = Akreditasi::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('superadmin.akreditasi.index', compact('akreditasis'));
    }

    // ============================================================
    // PENGAJUAN — superadmin ajukan untuk pesantren tertentu
    // ============================================================

    public function pengajuanForm()
    {
        $pesantren = User::whereHas('role', fn ($query) => $query->where('parameter', 'pesantren'))->get();
        return view('superadmin.akreditasi.pengajuan', compact('pesantren'));
    }

    public function submitPengajuan(Request $request)
    {
        $validated = $request->validate([
            'pesantren_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $this->workflowService->submitPengajuanAwal((int) $validated['pesantren_id']);

            session()->flash('success', 'Pengajuan akreditasi berhasil dibuat.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }


    // ============================================================
    // ADMIN ACTIONS — review awal, tahap 1, assign, validasi, SK
    // ============================================================

    public function reviewAwal($akreditasiId)
    {
        $akreditasi = Akreditasi::whereIn('status', [
            Akreditasi::STATUS_INITIAL_SUBMITTED,
        ])->findOrFail($akreditasiId);

        return view('admin.akreditasi.review-awal', compact('akreditasi'));
    }

    public function terimaPengajuan(Request $request, $akreditasiId)
    {
        try {
            $this->workflowService->adminReviewAwal($akreditasiId, auth()->id(), 'accept', $request->input('catatan'));
            session()->flash('success', 'Pengajuan akreditasi diterima.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function tolakPengajuan(Request $request, $akreditasiId)
    {
        $validated = $request->validate(['reason' => 'required|string|min:5']);

        try {
            $this->workflowService->adminReviewAwal($akreditasiId, auth()->id(), 'reject', $validated['reason']);
            session()->flash('success', 'Pengajuan akreditasi ditolak.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function bukaAssessment(Request $request, $akreditasiId)
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($request->isMethod('get')) {
            return view('admin.akreditasi.buka-assessment', compact('akreditasi'));
        }

        $validated = $request->validate([
            'deadline' => 'nullable|date|after:today',
        ]);

        try {
            if ($validated['deadline'] ?? null) {
                $akreditasi->forceFill([
                    'assessment_deadline' => $validated['deadline'],
                ])->save();
            }

            session()->flash('success', 'Assessment berhasil dibuka.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function reviewTahap1($akreditasiId)
    {
        $akreditasi = Akreditasi::whereIn('status', [
            Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW,
            Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW,
        ])->findOrFail($akreditasiId);

        return view('admin.akreditasi.review-tahap1', compact('akreditasi'));
    }

    public function mintaPerbaikanTahap1(Request $request, $akreditasiId)
    {
        $validated = $request->validate([
            'sections' => 'required|array|min:1',
            'sections.*' => 'string|in:ipm,sdm,edpm',
            'reason' => 'nullable|string',
        ]);

        try {
            $this->workflowService->adminStage1Review($akreditasiId, auth()->id(), 'correction', $validated['sections'], $validated['reason'] ?? null);
            session()->flash('success', 'Permintaan perbaikan tahap 1 dikirim.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function approveTahap1(Request $request, $akreditasiId)
    {
        try {
            $this->workflowService->adminStage1Review($akreditasiId, auth()->id(), 'approve', [], $request->input('catatan'));
            session()->flash('success', 'Tahap 1 disetujui.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function assignAsesor(Request $request, $akreditasiId)
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($request->isMethod('get')) {
            $asesors = User::where('role_id', 2)->get();
            $existing = Assessment::with('asesor')->where('akreditasi_id', $akreditasiId)->get();
            return view('admin.akreditasi.assign-asesor', compact('akreditasi', 'asesors', 'existing'));
        }

        $validated = $request->validate([
            'ketua_id' => 'required|integer|exists:users,id',
            'anggota_ids' => 'nullable|array',
            'anggota_ids.*' => 'integer|exists:users,id',
            'reason' => 'nullable|string',
        ]);

        try {
            $this->workflowService->adminAssignAsesor($akreditasiId, (int) $validated['ketua_id'], array_map('intval', $validated['anggota_ids'] ?? []), auth()->id());
            session()->flash('success', 'Asesor berhasil ditugaskan.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function reassignAsesor(Request $request, $akreditasiId)
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($request->isMethod('get')) {
            $asesors = User::where('role_id', 2)->get();
            $existing = Assessment::with('asesor')->where('akreditasi_id', $akreditasiId)->get();
            return view('admin.akreditasi.reassign-asesor', compact('akreditasi', 'asesors', 'existing'));
        }

        $validated = $request->validate([
            'ketua_id' => 'required|integer|exists:users,id',
            'anggota_ids' => 'nullable|array',
            'anggota_ids.*' => 'integer|exists:users,id',
            'reason' => 'nullable|string',
        ]);

        try {
            Assessment::where('akreditasi_id', $akreditasiId)->delete();
            $this->workflowService->adminAssignAsesor($akreditasiId, (int) $validated['ketua_id'], array_map('intval', $validated['anggota_ids'] ?? []), auth()->id());
            session()->flash('success', 'Asesor berhasil ditugaskan ulang.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function handleLimitReview(Request $request, $akreditasiId)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:approve_by_exception,reject_administrative',
            'reason' => 'required_if:action,reject_administrative|nullable|string',
        ]);

        try {
            $this->workflowService->adminHandleStage1Limit($akreditasiId, auth()->id(), $validated['action'], $validated['reason'] ?? $request->input('catatan'));
            session()->flash('success', 'Keputusan batas koreksi diproses.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    // ============================================================
    // ASESOR ACTIONS — review tahap 2, visitasi, nilai, laporan
    // ============================================================

    public function reviewTahap2($akreditasiId)
    {
        $akreditasi = Akreditasi::whereIn('status', [
            Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
            Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW,
        ])->findOrFail($akreditasiId);

        return view('asesor.ketua.review-tahap2', compact('akreditasi'));
    }

    public function nyatakanLayakVisitasi($akreditasiId)
    {
        try {
            $this->workflowService->ketuaAsesorStage2Review($akreditasiId, auth()->id(), 'approve');
            session()->flash('success', 'Akreditasi dinyatakan layak visitasi.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back();
        }
    }

    public function mintaPerbaikanTahap2(Request $request, $akreditasiId)
    {
        $validated = $request->validate([
            'sections' => 'required|array|min:1',
            'sections.*' => 'string|in:ipm,sdm,edpm',
            'reason' => 'nullable|string',
        ]);

        try {
            $this->workflowService->ketuaAsesorStage2Review($akreditasiId, auth()->id(), 'correction', $validated['sections'], $validated['reason'] ?? null);
            session()->flash('success', 'Permintaan perbaikan tahap 2 dikirim.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function jadwalkanVisitasi(Request $request, $akreditasiId)
    {
        $akreditasi = Akreditasi::whereIn('status', [
            Akreditasi::STATUS_VISITASI_SCHEDULED,
            Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW,
        ])->findOrFail($akreditasiId);

        if ($request->isMethod('get')) {
            return view('asesor.ketua.jadwalkan-visitasi', compact('akreditasi'));
        }

        $validated = $request->validate([
            'tgl_mulai' => 'required|date|after:today',
            'tgl_akhir' => 'required|date|after_or_equal:tgl_mulai',
            'catatan' => 'nullable|string',
        ]);

        try {
            $this->workflowService->ketuaJadwalkanVisitasi($akreditasiId, auth()->id(), $validated['tgl_mulai'], $validated['tgl_akhir'], $validated['catatan'] ?? null);
            session()->flash('success', 'Visitasi berhasil dijadwalkan.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function tandaiVisitasiSelesai($akreditasiId)
    {
        try {
            $this->workflowService->ketuaTandaiVisitasiSelesai($akreditasiId, auth()->id());
            session()->flash('success', 'Visitasi ditandai selesai.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back();
        }
    }

    public function inputNA1(Request $request, $akreditasiId)
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($request->isMethod('get')) {
            $komponen = \App\Models\MasterEdpmKomponen::with('butirs')->get();
            return view('asesor.ketua.input-na1', compact('akreditasi', 'komponen'));
        }

        $validated = $request->validate(['butir' => 'required|array', 'butir.*' => 'integer|min:0', 'set_final' => 'nullable|boolean']);

        try {
            $this->workflowService->submitNA1($akreditasiId, auth()->id(), $validated['butir'], (bool) ($validated['set_final'] ?? false));
            session()->flash('success', 'Nilai NA1 berhasil disimpan.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function inputNA2(Request $request, $akreditasiId)
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($request->isMethod('get')) {
            $komponen = \App\Models\MasterEdpmKomponen::with('butirs')->get();
            return view('asesor.anggota.input-na2', compact('akreditasi', 'komponen'));
        }

        $validated = $request->validate(['butir' => 'required|array', 'butir.*' => 'integer|min:0', 'set_final' => 'nullable|boolean']);

        try {
            $this->workflowService->submitNA2($akreditasiId, auth()->id(), $validated['butir'], (bool) ($validated['set_final'] ?? false));
            session()->flash('success', 'Nilai NA2 berhasil disimpan.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function inputNK(Request $request, $akreditasiId)
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($request->isMethod('get')) {
            $komponen = \App\Models\MasterEdpmKomponen::with('butirs')->get();
            return view('asesor.ketua.input-nk', compact('akreditasi', 'komponen'));
        }

        $validated = $request->validate(['butir' => 'required|array', 'butir.*' => 'integer|min:0', 'set_final' => 'nullable|boolean']);

        try {
            $this->workflowService->ketuaInputNK($akreditasiId, auth()->id(), $validated['butir'], (bool) ($validated['set_final'] ?? false));
            session()->flash('success', 'Nilai NK berhasil disimpan.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function uploadLaporan(Request $request, $akreditasiId)
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($request->isMethod('get')) {
            return view('asesor.ketua.upload-laporan', compact('akreditasi'));
        }

        $validated = $request->validate([
            'laporan_individu' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'laporan_kelompok' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        try {
            $this->workflowService->ketuaUploadLaporan($akreditasiId, auth()->id(), $validated['laporan_individu'], $validated['laporan_kelompok']);
            session()->flash('success', 'Laporan berhasil diupload.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function submitHasilVisitasi($akreditasiId)
    {
        try {
            $this->workflowService->ketuaSubmitHasilVisitasi($akreditasiId, auth()->id());
            session()->flash('success', 'Hasil visitasi berhasil dikirim.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back();
        }
    }

    // ============================================================
    // PESANTREN ACTIONS — upload KK
    // ============================================================

    public function uploadKartuKendali(Request $request, $akreditasiId)
    {
        try {
            $this->workflowService->pesantrenUploadKartuKendali($akreditasiId, auth()->id(), $request->file('file'));
            session()->flash('success', 'Kartu kendali berhasil diupload.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    // ============================================================
    // FINAL ACTIONS — validasi akhir, SK, banding
    // ============================================================

    public function validasiAkhir($akreditasiId)
    {
        $akreditasi = Akreditasi::whereIn('status', [
            Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED,
            Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
        ])->findOrFail($akreditasiId);

        $nkEntries = AkreditasiEdpm::where('akreditasi_id', $akreditasi->id)->get();

        return view('admin.akreditasi.validasi-akhir', compact('akreditasi', 'nkEntries'));
    }

    public function approveFinal(Request $request, $akreditasiId)
    {
        $validated = $request->validate([
            'nv_values' => 'nullable|array',
            'nv_values.*' => 'numeric|min:0|max:4',
            'reason' => 'nullable|string',
        ]);

        try {
            $this->workflowService->adminValidasiAkhir($akreditasiId, auth()->id(), true, $validated['reason'] ?? null, $validated['nv_values'] ?? null);
            session()->flash('success', 'Akreditasi disetujui.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back();
        }
    }

    public function tolakFinal(Request $request, $akreditasiId)
    {
        $validated = $request->validate(['reason' => 'required|string|min:5']);

        try {
            $this->workflowService->adminValidasiAkhir($akreditasiId, auth()->id(), false, $validated['reason']);
            session()->flash('success', 'Akreditasi ditolak.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function terbitkanSK(Request $request, $akreditasiId)
    {
        $validated = $request->validate([
            'nomor_sk' => 'required|string|max:100',
            'masa_berlaku' => 'required|string',
        ]);

        try {
            $this->workflowService->adminTerbitkanSK($akreditasiId, auth()->id(), $validated['nomor_sk'], $validated['masa_berlaku']);
            session()->flash('success', 'SK berhasil diterbitkan.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back();
        }
    }

    public function terimaBanding(Request $request, $id)
    {
        $validated = $request->validate([
            'response' => 'nullable|string',
        ]);

        try {
            $this->bandingService->processBanding($id, auth()->id(), 'accept', $validated['response'] ?? null);
            session()->flash('success', 'Banding diterima.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back();
        }
    }

    public function tolakBanding(Request $request, $id)
    {
        $validated = $request->validate([
            'response' => 'required|string|min:5',
        ]);

        try {
            $this->bandingService->processBanding($id, auth()->id(), 'reject', $validated['response']);
            session()->flash('success', 'Banding ditolak.');
            return redirect()->route('superadmin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back();
        }
    }
}

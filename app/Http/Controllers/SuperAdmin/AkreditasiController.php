<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use App\Models\AkreditasiEdpm;
use App\Models\Assessment;
use App\Models\Banding;
use App\Models\Document;
use App\Models\Edpm;
use App\Models\Ipm;
use App\Models\MasterEdpmKomponen;
use App\Models\Pesantren;
use App\Models\SdmPesantren;
use App\Models\User;
use App\Services\AkreditasiWorkflowService;
use App\Services\BandingService;
use App\Services\ScoringService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

    public function index(Request $request)
    {
        $period = $request->query('period', 'all');
        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('q', ''));

        $query = $this->akreditasiIndexQuery($period, $status, $search)
            ->with(['user.pesantren', 'assessments.asesor', 'bandings']);

        $akreditasis = $query->orderBy('created_at', 'desc')->get();
        $stats = $this->summaryStats(Akreditasi::query()->get());
        $statusOptions = Akreditasi::STATUS_LABELS;
        $statusColors = $this->statusColors();
        $periodOptions = $this->periodOptions();
        $nextStepLabels = $this->nextStepLabels();

        return view('superadmin.akreditasi.index', compact(
            'akreditasis',
            'stats',
            'statusOptions',
            'statusColors',
            'periodOptions',
            'period',
            'status',
            'search',
            'nextStepLabels',
        ));
    }

    public function export(Request $request)
    {
        $period = $request->query('period', 'all');
        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('q', ''));
        $akreditasis = $this->akreditasiIndexQuery($period, $status, $search)
            ->with(['user.pesantren', 'assessments.asesor'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->streamDownload(function () use ($akreditasis) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['UUID', 'Pesantren', 'Email', 'Status', 'Asesor', 'Siklus', 'NA1', 'NA2', 'NK', 'NV', 'Nilai', 'Peringkat', 'Tanggal']);

            foreach ($akreditasis as $akreditasi) {
                fputcsv($out, [
                    $akreditasi->uuid,
                    $akreditasi->user?->pesantren?->nama_pesantren ?? $akreditasi->user?->name ?? '-',
                    $akreditasi->user?->email ?? '-',
                    $akreditasi->getStatusLabel(),
                    $akreditasi->assessments->map(fn (Assessment $assessment) => ($assessment->tipe ? strtoupper($assessment->tipe).': ' : '').($assessment->asesor?->name ?? '-'))->implode(' | '),
                    $akreditasi->correction_cycle ?? 0,
                    $akreditasi->na1 ?? '-',
                    $akreditasi->na2 ?? '-',
                    $akreditasi->nk ?? '-',
                    $akreditasi->nv ?? '-',
                    $akreditasi->nilai ?? '-',
                    $akreditasi->peringkat ?? '-',
                    $akreditasi->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($out);
        }, 'akreditasi-superadmin.csv', ['Content-Type' => 'text/csv']);
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

    public function show($akreditasiId)
    {
        $akreditasi = Akreditasi::with([
            'user.pesantren.units',
            'assessments.asesor',
            'bandings.user',
            'bandings.processor',
            'auditLogs.user',
        ])->findOrFail($akreditasiId);

        $userId = $akreditasi->user_id;
        $pesantren = Pesantren::with('units')->where('user_id', $userId)->first();
        $ipm = Ipm::where('user_id', $userId)->first();
        $sdm = SdmPesantren::where('user_id', $userId)->first();
        $edpm = Edpm::where('user_id', $userId)->first();
        $documents = Document::with(['category', 'uploader'])
            ->where('akreditasi_id', $akreditasi->id)
            ->latest()
            ->get();
        $edpmScores = AkreditasiEdpm::with(['asesor', 'butir'])
            ->where('akreditasi_id', $akreditasi->id)
            ->latest()
            ->get()
            ->groupBy('type');
        $actorUsers = User::whereIn('id', $akreditasi->auditLogs->pluck('actor_user_id')->filter()->unique())
            ->get()
            ->keyBy('id');
        $actions = $this->availableActions($akreditasi);
        $statusColors = $this->statusColors();
        $dataCompleteness = [
            'profil' => (bool) $pesantren,
            'unit' => (bool) ($pesantren?->units?->isNotEmpty()),
            'ipm' => (bool) $ipm,
            'sdm' => (bool) $sdm,
            'edpm' => (bool) $edpm,
        ];
        $documentFields = [
            'dok_profil' => 'Dokumen Profil',
            'dok_nsp' => 'Sertifikat NSP',
            'dok_renstra' => 'Renstra',
            'dok_rk_anggaran' => 'RK Anggaran',
            'dok_kurikulum' => 'Kurikulum',
            'dok_silabus_rpp' => 'Silabus/RPP',
            'dok_kepengasuhan' => 'Kepengasuhan',
            'dok_peraturan_kepegawaian' => 'Peraturan Kepegawaian',
            'dok_sarpras' => 'Sarpras',
            'dok_laporan_tahunan' => 'Laporan Tahunan',
            'dok_sop' => 'SOP',
        ];

        return view('superadmin.akreditasi.show', compact(
            'akreditasi',
            'pesantren',
            'ipm',
            'sdm',
            'edpm',
            'documents',
            'edpmScores',
            'actorUsers',
            'actions',
            'statusColors',
            'dataCompleteness',
            'documentFields',
        ));
    }

    // ============================================================
    // ADMIN ACTIONS — review awal, tahap 1, assign, validasi, SK
    // ============================================================

    public function reviewAwal($akreditasiId)
    {
        $akreditasi = Akreditasi::whereIn('status', [
            Akreditasi::STATUS_INITIAL_SUBMITTED,
        ])->findOrFail($akreditasiId);

        return view('admin.akreditasi.review-awal', $this->superadminViewData(compact('akreditasi')));
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
            return view('admin.akreditasi.buka-assessment', $this->superadminViewData(compact('akreditasi')));
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

        return view('admin.akreditasi.review-tahap1', $this->superadminViewData(compact('akreditasi')));
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

            return view('admin.akreditasi.assign-asesor', $this->superadminViewData(compact('akreditasi', 'asesors', 'existing')));
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

            return view('admin.akreditasi.reassign-asesor', $this->superadminViewData(compact('akreditasi', 'asesors', 'existing')));
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

        return view('asesor.ketua.review-tahap2', $this->superadminViewData(compact('akreditasi'), [
            'approveRouteName' => 'superadmin.akreditasi.layak-visitasi',
            'correctionRouteName' => 'superadmin.akreditasi.minta-perbaikan-tahap2',
        ]));
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
            return view('asesor.ketua.jadwalkan-visitasi', $this->superadminViewData(compact('akreditasi'), [
                'scheduleRouteName' => 'superadmin.akreditasi.jadwalkan-visitasi',
            ]));
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
            $komponen = MasterEdpmKomponen::with('butirs')->get();

            return view('asesor.ketua.input-na1', $this->superadminViewData(compact('akreditasi', 'komponen'), [
                'inputRouteName' => 'superadmin.akreditasi.input-na1',
            ]));
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
            $komponen = MasterEdpmKomponen::with('butirs')->get();

            return view('asesor.anggota.input-na2', $this->superadminViewData(compact('akreditasi', 'komponen'), [
                'inputRouteName' => 'superadmin.akreditasi.input-na2',
            ]));
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
            $komponen = MasterEdpmKomponen::with('butirs')->get();

            return view('asesor.ketua.input-nk', $this->superadminViewData(compact('akreditasi', 'komponen'), [
                'inputRouteName' => 'superadmin.akreditasi.input-nk',
            ]));
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
            return view('asesor.ketua.upload-laporan', $this->superadminViewData(compact('akreditasi'), [
                'uploadRouteName' => 'superadmin.akreditasi.upload-laporan',
            ]));
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

        return view('admin.akreditasi.validasi-akhir', $this->superadminViewData(compact('akreditasi', 'nkEntries')));
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

    public function banding($akreditasiId)
    {
        $akreditasi = Akreditasi::with(['user.pesantren', 'bandings'])
            ->where('status', Akreditasi::STATUS_APPEAL_SUBMITTED)
            ->findOrFail($akreditasiId);
        $bandingRoutePrefix = 'superadmin.banding';

        return view('admin.akreditasi.banding', $this->superadminViewData(
            compact('akreditasi', 'bandingRoutePrefix')
        ));
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

    private function superadminViewData(array $data, array $routes = []): array
    {
        return $data + $routes + [
            'akreditasiRoutePrefix' => 'superadmin.akreditasi',
            'backRouteName' => 'superadmin.akreditasi.index',
            'isSuperAdminView' => true,
            'superadminBackRoute' => route('superadmin.akreditasi.index'),
        ];
    }

    private function nextStepLabels(): array
    {
        return [
            Akreditasi::STATUS_DRAFT_PROFILE => 'Lengkapi profil pesantren',
            Akreditasi::STATUS_INITIAL_SUBMITTED => 'Review pengajuan awal',
            Akreditasi::STATUS_ASSESSMENT_OPEN => 'Menunggu assessment pesantren',
            Akreditasi::STATUS_INITIAL_REJECTED => 'Menunggu resubmit pesantren',
            Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW => 'Review administrasi tahap 1',
            Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION => 'Menunggu perbaikan tahap 1',
            Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW => 'Putuskan batas koreksi tahap 1',
            Akreditasi::STATUS_ASSESSOR_ASSIGNMENT => 'Tugaskan asesor',
            Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW => 'Review asesor tahap 2',
            Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION => 'Menunggu perbaikan tahap 2',
            Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW => 'Putuskan batas koreksi tahap 2',
            Akreditasi::STATUS_VISITASI_SCHEDULED => 'Pantau jadwal visitasi',
            Akreditasi::STATUS_VISITASI_COMPLETED => 'Input nilai pasca visitasi',
            Akreditasi::STATUS_POST_VISITASI_SCORING => 'Lengkapi NA1, NA2, dan NK',
            Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED => 'Validasi hasil visitasi',
            Akreditasi::STATUS_ADMIN_FINAL_VALIDATION => 'Finalisasi validasi akhir',
            Akreditasi::STATUS_ADMINISTRATIVE_REJECTED => 'Menunggu tindak lanjut administratif',
            Akreditasi::STATUS_FINAL_APPROVED => 'Terbitkan SK / selesaikan',
            Akreditasi::STATUS_FINAL_REJECTED => 'Menunggu potensi banding',
            Akreditasi::STATUS_APPEAL_SUBMITTED => 'Proses permohonan banding',
            Akreditasi::STATUS_COMPLETED => 'Selesai',
        ];
    }

    private function statusColors(): array
    {
        return [
            Akreditasi::STATUS_DRAFT_PROFILE => 'secondary',
            Akreditasi::STATUS_INITIAL_SUBMITTED => 'primary',
            Akreditasi::STATUS_ASSESSMENT_OPEN => 'info',
            Akreditasi::STATUS_INITIAL_REJECTED => 'danger',
            Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW => 'warning',
            Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION => 'warning',
            Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW => 'warning',
            Akreditasi::STATUS_ASSESSOR_ASSIGNMENT => 'info',
            Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW => 'warning',
            Akreditasi::STATUS_ASSESSOR_STAGE_2_CORRECTION => 'warning',
            Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW => 'warning',
            Akreditasi::STATUS_VISITASI_SCHEDULED => 'info',
            Akreditasi::STATUS_VISITASI_COMPLETED => 'info',
            Akreditasi::STATUS_POST_VISITASI_SCORING => 'danger',
            Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED => 'primary',
            Akreditasi::STATUS_ADMIN_FINAL_VALIDATION => 'warning',
            Akreditasi::STATUS_ADMINISTRATIVE_REJECTED => 'danger',
            Akreditasi::STATUS_FINAL_APPROVED => 'success',
            Akreditasi::STATUS_FINAL_REJECTED => 'danger',
            Akreditasi::STATUS_APPEAL_SUBMITTED => 'warning',
            Akreditasi::STATUS_COMPLETED => 'success',
        ];
    }

    private function summaryStats($akreditasis): array
    {
        return [
            'total' => $akreditasis->count(),
            'active' => $akreditasis->whereNotIn('status', Akreditasi::TERMINAL_STATUSES)->count(),
            'completed' => $akreditasis->whereIn('status', Akreditasi::TERMINAL_STATUSES)->count(),
            'appeal' => $akreditasis->where('status', Akreditasi::STATUS_APPEAL_SUBMITTED)->count(),
            'overdue' => $akreditasis
                ->filter(fn (Akreditasi $akreditasi) => $akreditasi->assessment_deadline && $akreditasi->assessment_deadline->isPast() && ! $akreditasi->isTerminal())
                ->count(),
        ];
    }

    private function periodOptions(): array
    {
        $years = Akreditasi::query()
            ->orderByDesc('created_at')
            ->pluck('created_at')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->format('Y'))
            ->unique()
            ->mapWithKeys(fn ($year) => [(string) $year => (string) $year])
            ->all();

        return ['all' => 'Semua Periode'] + $years;
    }

    private function akreditasiIndexQuery(string $period, string $status, string $search)
    {
        return Akreditasi::query()
            ->when($period !== 'all', fn ($q) => $q->whereYear('created_at', (int) $period))
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('uuid', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('user.pesantren', fn ($pesantrenQuery) => $pesantrenQuery
                            ->where('nama_pesantren', 'like', "%{$search}%")
                            ->orWhere('ns_pesantren', 'like', "%{$search}%"));
                });
            });
    }

    private function availableActions(Akreditasi $akreditasi): array
    {
        $actions = [];

        if ($akreditasi->status === Akreditasi::STATUS_INITIAL_SUBMITTED) {
            $actions[] = ['label' => 'Review Awal', 'route' => route('superadmin.akreditasi.review-awal', $akreditasi), 'color' => 'primary'];
        }
        if ($akreditasi->status === Akreditasi::STATUS_ASSESSMENT_OPEN) {
            $actions[] = ['label' => 'Buka/Atur Assessment', 'route' => route('superadmin.akreditasi.buka-assessment', $akreditasi), 'color' => 'info'];
        }
        if (in_array($akreditasi->status, [Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW, Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW], true)) {
            $actions[] = ['label' => 'Review Tahap 1', 'route' => route('superadmin.akreditasi.review-tahap1', $akreditasi), 'color' => 'warning'];
        }
        if ($akreditasi->status === Akreditasi::STATUS_ASSESSOR_ASSIGNMENT) {
            $actions[] = ['label' => 'Assign Asesor', 'route' => route('superadmin.akreditasi.assign-asesor', $akreditasi), 'color' => 'info'];
        }
        if (in_array($akreditasi->status, [Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW, Akreditasi::STATUS_ASSESSOR_STAGE_2_LIMIT_REVIEW], true)) {
            $actions[] = ['label' => 'Review Tahap 2', 'route' => route('superadmin.akreditasi.review-tahap2', $akreditasi), 'color' => 'warning'];
            $actions[] = ['label' => 'Jadwalkan Visitasi', 'route' => route('superadmin.akreditasi.jadwalkan-visitasi', $akreditasi), 'color' => 'info'];
        }
        if ($akreditasi->status === Akreditasi::STATUS_VISITASI_SCHEDULED) {
            $actions[] = ['label' => 'Jadwal Visitasi', 'route' => route('superadmin.akreditasi.jadwalkan-visitasi', $akreditasi), 'color' => 'info'];
        }
        if ($akreditasi->status === Akreditasi::STATUS_POST_VISITASI_SCORING) {
            $actions[] = ['label' => 'Input NA1', 'route' => route('superadmin.akreditasi.input-na1', $akreditasi), 'color' => 'danger'];
            $actions[] = ['label' => 'Input NA2', 'route' => route('superadmin.akreditasi.input-na2', $akreditasi), 'color' => 'danger'];
            $actions[] = ['label' => 'Input NK', 'route' => route('superadmin.akreditasi.input-nk', $akreditasi), 'color' => 'danger'];
            $actions[] = ['label' => 'Upload Laporan', 'route' => route('superadmin.akreditasi.upload-laporan', $akreditasi), 'color' => 'primary'];
        }
        if (in_array($akreditasi->status, [Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED, Akreditasi::STATUS_ADMIN_FINAL_VALIDATION], true)) {
            $actions[] = ['label' => 'Validasi Akhir', 'route' => route('superadmin.akreditasi.validasi-akhir', $akreditasi), 'color' => 'success'];
        }
        if ($akreditasi->status === Akreditasi::STATUS_APPEAL_SUBMITTED) {
            $pendingBanding = $akreditasi->bandings->firstWhere('status', 'pending');
            if ($pendingBanding) {
                $actions[] = ['label' => 'Proses Banding', 'route' => route('superadmin.akreditasi.banding', $akreditasi), 'color' => 'warning'];
            }
        }

        return $actions;
    }
}

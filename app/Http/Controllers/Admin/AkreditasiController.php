<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use App\Models\AkreditasiEdpm;
use App\Models\Assessment;
use App\Models\User;
use App\Services\AkreditasiWorkflowService;
use App\Services\BandingService;
use App\Services\ScoringService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AkreditasiController extends Controller
{
    public function __construct(
        private AkreditasiWorkflowService $workflowService,
        private BandingService $bandingService,
        private ScoringService $scoringService,
    ) {}

    public function index(Request $request)
    {
        $period = $request->query('period', 'all');
        $akreditasis = $this->akreditasiIndexQuery($period)
            ->orderBy('created_at', 'desc')
            ->get();

        $periodOptions = $this->periodOptions();

        return view('admin.akreditasi.index', compact('akreditasis', 'period', 'periodOptions'));
    }

    public function export(Request $request)
    {
        $period = $request->query('period', 'all');
        $tab = $request->query('tab', 'semua');
        $query = $this->akreditasiIndexQuery($period);

        $tabStatuses = [
            'pengajuan' => [Akreditasi::STATUS_INITIAL_SUBMITTED, Akreditasi::STATUS_INITIAL_REJECTED],
            'review-tahap1' => [Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW, Akreditasi::STATUS_ADMIN_STAGE_1_CORRECTION, Akreditasi::STATUS_ADMIN_STAGE_1_LIMIT_REVIEW],
            'assign' => [Akreditasi::STATUS_ASSESSOR_ASSIGNMENT],
            'validasi-akhir' => [Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED, Akreditasi::STATUS_ADMIN_FINAL_VALIDATION],
            'banding' => [Akreditasi::STATUS_APPEAL_SUBMITTED],
        ];

        if (isset($tabStatuses[$tab])) {
            $query->whereIn('status', $tabStatuses[$tab]);
        }

        $akreditasis = $query->with('user.pesantren')->orderBy('created_at', 'desc')->get();

        return response()->streamDownload(function () use ($akreditasis) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['UUID', 'Pesantren', 'Status', 'Siklus', 'Nilai', 'Peringkat', 'Tanggal']);

            foreach ($akreditasis as $akreditasi) {
                fputcsv($out, [
                    $akreditasi->uuid,
                    $akreditasi->user?->pesantren?->nama_pesantren ?? '-',
                    $akreditasi->getStatusLabel(),
                    $akreditasi->correction_cycle ?? 0,
                    $akreditasi->nilai ?? '-',
                    $akreditasi->peringkat ?? '-',
                    $akreditasi->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($out);
        }, 'akreditasi-admin.csv', ['Content-Type' => 'text/csv']);
    }

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
            $this->workflowService->adminReviewAwal(
                $akreditasiId,
                auth()->id(),
                'accept',
                $request->input('catatan')
            );

            session()->flash('success', 'Pengajuan akreditasi diterima. Assessment siap dibuka.');

            return redirect()->route('admin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function tolakPengajuan(Request $request, $akreditasiId)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        try {
            $this->workflowService->adminReviewAwal(
                $akreditasiId,
                auth()->id(),
                'reject',
                $validated['reason']
            );

            session()->flash('success', 'Pengajuan akreditasi ditolak.');

            return redirect()->route('admin.akreditasi.index');
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

            return redirect()->route('admin.akreditasi.index');
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
            $this->workflowService->adminStage1Review(
                $akreditasiId,
                auth()->id(),
                'correction',
                $validated['sections'],
                $validated['reason'] ?? null
            );

            session()->flash('success', 'Permintaan perbaikan tahap 1 telah dikirim.');

            return redirect()->route('admin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function approveTahap1(Request $request, $akreditasiId)
    {
        try {
            $this->workflowService->adminStage1Review(
                $akreditasiId,
                auth()->id(),
                'approve',
                [],
                $request->input('catatan')
            );

            session()->flash('success', 'Tahap 1 disetujui. Lanjutkan ke penugasan asesor.');

            return redirect()->route('admin.akreditasi.index');
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
            $existing = Assessment::with('asesor')
                ->where('akreditasi_id', $akreditasiId)
                ->get();

            return view('admin.akreditasi.assign-asesor', compact('akreditasi', 'asesors', 'existing'));
        }

        $validated = $request->validate([
            'ketua_id' => 'required|integer|exists:users,id',
            'anggota_ids' => 'nullable|array',
            'anggota_ids.*' => 'integer|exists:users,id',
        ]);

        try {
            $this->workflowService->adminAssignAsesor(
                $akreditasiId,
                (int) $validated['ketua_id'],
                array_map('intval', $validated['anggota_ids'] ?? []),
                auth()->id()
            );

            session()->flash('success', 'Asesor berhasil ditugaskan.');

            return redirect()->route('admin.akreditasi.index');
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
            $existing = Assessment::with('asesor')
                ->where('akreditasi_id', $akreditasiId)
                ->get();

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

            $this->workflowService->adminAssignAsesor(
                $akreditasiId,
                (int) $validated['ketua_id'],
                array_map('intval', $validated['anggota_ids'] ?? []),
                auth()->id()
            );

            session()->flash('success', 'Asesor berhasil ditugaskan ulang.');

            return redirect()->route('admin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function handleLimitReview(Request $request, $akreditasiId)
    {
        $validated = $request->validate([
            'action' => 'nullable|string|in:approve_by_exception,reject_administrative,default',
            'reason' => 'required_if:action,reject_administrative|nullable|string',
        ]);

        try {
            $this->workflowService->adminHandleStage1Limit(
                $akreditasiId,
                auth()->id(),
                $validated['action'] ?? 'default',
                $validated['reason'] ?? $request->input('catatan')
            );

            session()->flash('success', 'Keputusan batas koreksi berhasil diproses.');

            return redirect()->route('admin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function validasiAkhir($akreditasiId)
    {
        $akreditasi = Akreditasi::whereIn('status', [
            Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED,
            Akreditasi::STATUS_ADMIN_FINAL_VALIDATION,
        ])->findOrFail($akreditasiId);

        $nkEntries = AkreditasiEdpm::where('akreditasi_id', $akreditasi->id)
            ->where('type', 'nk')
            ->get()
            ->keyBy('butir_id');

        return view('admin.akreditasi.validasi-akhir', compact('akreditasi', 'nkEntries'));
    }

    public function approveFinal(Request $request, $akreditasiId)
    {
        $validated = $request->validate([
            'nv_values' => 'nullable|array',
            'nv_values.*' => 'numeric|min:0|max:4',
            'nv_reasons' => 'nullable|array',
            'nv_reasons.*' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        try {
            $this->workflowService->adminValidasiAkhir(
                $akreditasiId,
                auth()->id(),
                true,
                $validated['reason'] ?? null,
                $validated['nv_values'] ?? null,
                $validated['nv_reasons'] ?? null
            );

            session()->flash('success', 'Validasi akhir disetujui. Akreditasi telah final.');

            return redirect()->route('admin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function tolakFinal(Request $request, $akreditasiId)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        try {
            $this->workflowService->adminValidasiAkhir(
                $akreditasiId,
                auth()->id(),
                false,
                $validated['reason'],
                null
            );

            session()->flash('success', 'Validasi akhir ditolak. Pesantren dapat mengajukan banding.');

            return redirect()->route('admin.akreditasi.index');
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
            $this->workflowService->adminTerbitkanSK(
                $akreditasiId,
                auth()->id(),
                $validated['nomor_sk'],
                $validated['masa_berlaku']
            );

            session()->flash('success', 'SK berhasil diterbitkan.');

            return redirect()->route('admin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function banding($akreditasiId)
    {
        $akreditasi = Akreditasi::with(['user.pesantren', 'bandings'])
            ->where('status', Akreditasi::STATUS_APPEAL_SUBMITTED)
            ->findOrFail($akreditasiId);

        return view('admin.akreditasi.banding', compact('akreditasi'));
    }

    public function terimaBanding(Request $request, $bandingId)
    {
        $validated = $request->validate([
            'response' => 'nullable|string',
        ]);

        try {
            $this->bandingService->processBanding(
                $bandingId,
                auth()->id(),
                'accept',
                $validated['response'] ?? null
            );

            session()->flash('success', 'Banding diterima. Kembali ke validasi akhir.');

            return redirect()->route('admin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function tolakBanding(Request $request, $bandingId)
    {
        $validated = $request->validate([
            'response' => 'required|string|min:5',
        ]);

        try {
            $this->bandingService->processBanding(
                $bandingId,
                auth()->id(),
                'reject',
                $validated['response']
            );

            session()->flash('success', 'Banding ditolak.');

            return redirect()->route('admin.akreditasi.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    private function akreditasiIndexQuery(string $period)
    {
        $query = Akreditasi::whereNotIn('status', Akreditasi::TERMINAL_STATUSES);

        if ($period !== 'all') {
            $query->whereYear('created_at', (int) $period);
        }

        return $query;
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
}

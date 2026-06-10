<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use App\Models\Assessment;
use App\Models\MasterEdpmKomponen;
use App\Services\AkreditasiWorkflowService;
use Exception;
use Illuminate\Http\Request;

class KetuaAsesorController extends Controller
{
    public function __construct(
        private AkreditasiWorkflowService $workflowService,
    ) {}

    public function index()
    {
        $akreditasiIds = Assessment::where('asesor_id', auth()->id())
            ->where('tipe', 'ketua')
            ->pluck('akreditasi_id');

        $akreditasis = Akreditasi::whereIn('id', $akreditasiIds)
            ->whereNotIn('status', Akreditasi::TERMINAL_STATUSES)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('asesor.ketua.index', compact('akreditasis'));
    }

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
            $this->workflowService->ketuaAsesorStage2Review(
                $akreditasiId,
                auth()->id(),
                'approve',
            );

            session()->flash('success', 'Akreditasi dinyatakan layak visitasi.');
            return redirect()->route('asesor.ketua.index');
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
            $this->workflowService->ketuaAsesorStage2Review(
                $akreditasiId,
                auth()->id(),
                'correction',
                $validated['sections'],
                $validated['reason'] ?? null
            );

            session()->flash('success', 'Permintaan perbaikan tahap 2 telah dikirim.');
            return redirect()->route('asesor.ketua.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function jadwalkanVisitasi(Request $request, $akreditasiId)
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($request->isMethod('get')) {
            return view('asesor.ketua.jadwalkan-visitasi', compact('akreditasi'));
        }

        $validated = $request->validate([
            'tgl_mulai' => 'required|date|after:today',
            'tgl_akhir' => 'required|date|after_or_equal:tgl_mulai',
            'catatan' => 'nullable|string',
        ]);

        try {
            $this->workflowService->ketuaJadwalkanVisitasi(
                $akreditasiId,
                auth()->id(),
                $validated['tgl_mulai'],
                $validated['tgl_akhir'],
                $validated['catatan'] ?? null
            );

            session()->flash('success', 'Jadwal visitasi berhasil ditetapkan.');
            return redirect()->route('asesor.ketua.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function tandaiVisitasiSelesai($akreditasiId)
    {
        try {
            $this->workflowService->ketuaTandaiVisitasiSelesai(
                $akreditasiId,
                auth()->id(),
            );

            session()->flash('success', 'Visitasi telah ditandai selesai.');
            return redirect()->route('asesor.ketua.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back();
        }
    }

    public function inputNA1(Request $request, $akreditasiId)
    {
        $akreditasi = Akreditasi::where('status', Akreditasi::STATUS_POST_VISITASI_SCORING)
            ->findOrFail($akreditasiId);

        $komponen = MasterEdpmKomponen::with('butirs')->get();

        if ($request->isMethod('get')) {
            return view('asesor.ketua.input-na1', compact('akreditasi', 'komponen'));
        }

        $validated = $request->validate([
            'butir' => 'required|array',
            'butir.*' => 'integer|min:0',
            'set_final' => 'nullable|boolean',
        ]);

        try {
            $this->workflowService->submitNA1(
                $akreditasiId,
                auth()->id(),
                $validated['butir'],
                (bool) ($validated['set_final'] ?? false)
            );

            session()->flash('success', 'Nilai NA1 berhasil disimpan.');
            return redirect()->route('asesor.ketua.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function inputNK(Request $request, $akreditasiId)
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if (! $akreditasi->is_na1_final || ! $akreditasi->is_na2_final) {
            session()->flash('error', 'Nilai NK hanya dapat diinput setelah NA1 dan NA2 ditetapkan final.');
            return redirect()->route('asesor.ketua.index');
        }

        $komponen = MasterEdpmKomponen::with('butirs')->get();

        if ($request->isMethod('get')) {
            return view('asesor.ketua.input-nk', compact('akreditasi', 'komponen'));
        }

        $validated = $request->validate([
            'butir' => 'required|array',
            'butir.*' => 'integer|min:0',
            'set_final' => 'nullable|boolean',
        ]);

        try {
            $this->workflowService->ketuaInputNK(
                $akreditasiId,
                auth()->id(),
                $validated['butir'],
                (bool) ($validated['set_final'] ?? false)
            );

            session()->flash('success', 'Nilai NK berhasil disimpan.');
            return redirect()->route('asesor.ketua.index');
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
            $this->workflowService->ketuaUploadLaporan(
                $akreditasiId,
                auth()->id(),
                $validated['laporan_individu'],
                $validated['laporan_kelompok']
            );

            session()->flash('success', 'Laporan visitasi berhasil diupload.');
            return redirect()->route('asesor.ketua.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function submitHasilVisitasi($akreditasiId)
    {
        try {
            $this->workflowService->ketuaSubmitHasilVisitasi(
                $akreditasiId,
                auth()->id(),
            );

            session()->flash('success', 'Hasil visitasi berhasil diserahkan.');
            return redirect()->route('asesor.ketua.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back();
        }
    }
}

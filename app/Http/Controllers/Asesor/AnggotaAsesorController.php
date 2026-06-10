<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use App\Models\Assessment;
use App\Models\MasterEdpmKomponen;
use App\Services\AkreditasiWorkflowService;
use Exception;
use Illuminate\Http\Request;

class AnggotaAsesorController extends Controller
{
    public function __construct(
        private AkreditasiWorkflowService $workflowService,
    ) {}

    public function index()
    {
        $akreditasiIds = Assessment::where('asesor_id', auth()->id())
            ->where('tipe', 'anggota')
            ->pluck('akreditasi_id');

        $akreditasis = Akreditasi::whereIn('id', $akreditasiIds)
            ->whereNotIn('status', Akreditasi::TERMINAL_STATUSES)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('asesor.anggota.index', compact('akreditasis'));
    }

    public function inputNA2(Request $request, $akreditasiId)
    {
        $akreditasi = Akreditasi::where('status', Akreditasi::STATUS_POST_VISITASI_SCORING)
            ->findOrFail($akreditasiId);

        $komponen = MasterEdpmKomponen::with('butirs')->get();

        if ($request->isMethod('get')) {
            return view('asesor.anggota.input-na2', compact('akreditasi', 'komponen'));
        }

        $validated = $request->validate([
            'butir' => 'required|array',
            'butir.*' => 'integer|min:0',
            'set_final' => 'nullable|boolean',
        ]);

        try {
            $this->workflowService->submitNA2(
                $akreditasiId,
                auth()->id(),
                $validated['butir'],
                (bool) ($validated['set_final'] ?? false)
            );

            session()->flash('success', 'Nilai NA2 berhasil disimpan.');
            return redirect()->route('asesor.anggota.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function uploadLaporanIndividu(Request $request, $akreditasiId)
    {
        $akreditasi = Akreditasi::findOrFail($akreditasiId);

        if ($request->isMethod('get')) {
            return view('asesor.anggota.upload-laporan-individu', compact('akreditasi'));
        }

        $validated = $request->validate([
            'laporan' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        try {
            $this->workflowService->anggotaUploadLaporanIndividu(
                $akreditasiId,
                auth()->id(),
                $validated['laporan']
            );

            session()->flash('success', 'Laporan individu berhasil diupload.');
            return redirect()->route('asesor.anggota.index');
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
}

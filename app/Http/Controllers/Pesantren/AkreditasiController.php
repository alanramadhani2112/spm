<?php

namespace App\Http\Controllers\Pesantren;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use App\Services\AkreditasiWorkflowService;
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
        return view('pesantren.akreditasi.pengajuan');
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

        return view('pesantren.akreditasi.assessment', compact('akreditasi'));
    }

    public function submitAssessment(Request $request, $akreditasiId)
    {
        try {
            Akreditasi::where('user_id', auth()->id())->findOrFail($akreditasiId);

            app(AkreditasiWorkflowService::class)->pesantrenSubmitAssessment(
                $akreditasiId,
                $request->except('_token')
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

        return view('pesantren.akreditasi.koreksi', compact('akreditasi'));
    }

    public function submitCorrection(Request $request, $akreditasiId)
    {
        try {
            $akreditasi = Akreditasi::where('user_id', auth()->id())->findOrFail($akreditasiId);

            $correctionData = $request->except('_token');

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
}

<?php

namespace Tests\Feature\AkreditasiWorkflow;

use App\Models\Akreditasi;
use App\Models\AkreditasiEdpm;
use App\Models\Document;
use App\Models\Edpm;
use App\Models\Ipm;
use App\Models\MasterEdpmButir;
use App\Models\Pesantren;
use App\Models\PesantrenUnit;
use App\Models\SdmPesantren;
use App\Models\User;
use App\Services\AkreditasiWorkflowService;
use App\Services\DocumentService;
use Database\Seeders\MasterEdpmSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EndToEndWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_akreditasi_workflow_reaches_completed_status(): void
    {
        Storage::fake('local');
        $this->seed(MasterEdpmSeeder::class);

        $admin = User::factory()->create(['role_id' => 1]);
        $ketuaAsesor = User::factory()->create(['role_id' => 2]);
        $anggotaAsesor = User::factory()->create(['role_id' => 2]);
        $pesantrenUser = User::factory()->create(['role_id' => 3]);
        $this->createCompletePesantrenData($pesantrenUser);

        $workflow = app(AkreditasiWorkflowService::class);

        $akreditasi = $workflow->submitPengajuanAwal($pesantrenUser->id);
        $this->assertSame(Akreditasi::STATUS_INITIAL_SUBMITTED, $akreditasi->status);

        $akreditasi = $workflow->adminReviewAwal($akreditasi->id, $admin->id, 'accept');
        $this->assertSame(Akreditasi::STATUS_ASSESSMENT_OPEN, $akreditasi->status);

        $akreditasi = $workflow->pesantrenSubmitAssessment($akreditasi->id, [
            'ipm' => ['santri_mukim' => 120, 'butir_1' => 'sesuai', 'butir_2' => 'sesuai', 'butir_3' => 'sesuai', 'butir_4' => 'sesuai'],
            'sdm' => ['butirs' => ['MI' => ['ustaz_tetap_L' => 14, 'ustaz_tetap_P' => 0]]],
            'edpm' => ['self_assessment' => 'lengkap'],
        ]);
        $this->assertSame(Akreditasi::STATUS_ADMIN_STAGE_1_REVIEW, $akreditasi->status);
        $this->assertSame(['santri_mukim' => 120, 'butir_1' => 'sesuai', 'butir_2' => 'sesuai', 'butir_3' => 'sesuai', 'butir_4' => 'sesuai'], Ipm::where('user_id', $pesantrenUser->id)->first()->data);

        $akreditasi = $workflow->adminStage1Review($akreditasi->id, $admin->id, 'approve');
        $this->assertSame(Akreditasi::STATUS_ASSESSOR_ASSIGNMENT, $akreditasi->status);

        $akreditasi = $workflow->adminAssignAsesor(
            $akreditasi->id,
            $ketuaAsesor->id,
            [$anggotaAsesor->id],
            $admin->id
        );
        $this->assertSame(Akreditasi::STATUS_ASSESSOR_STAGE_2_REVIEW, $akreditasi->status);
        $this->assertDatabaseHas('assessments', [
            'akreditasi_id' => $akreditasi->id,
            'asesor_id' => $ketuaAsesor->id,
            'tipe' => 'ketua',
        ]);

        $akreditasi = $workflow->ketuaJadwalkanVisitasi(
            $akreditasi->id,
            $ketuaAsesor->id,
            '2026-07-01',
            '2026-07-03',
            'Visitasi gelombang utama'
        );
        $this->assertSame(Akreditasi::STATUS_VISITASI_SCHEDULED, $akreditasi->status);

        $akreditasi = $workflow->ketuaTandaiVisitasiSelesai($akreditasi->id, $ketuaAsesor->id);
        $this->assertSame(Akreditasi::STATUS_POST_VISITASI_SCORING, $akreditasi->status);

        $scores = $this->scorePayload();
        $akreditasi = $workflow->submitNA1($akreditasi->id, $ketuaAsesor->id, $scores, true);
        $this->assertTrue($akreditasi->is_na1_final);

        $akreditasi = $workflow->submitNA2($akreditasi->id, $anggotaAsesor->id, $scores, true);
        $this->assertTrue($akreditasi->is_na2_final);

        $akreditasi = $workflow->ketuaInputNK($akreditasi->id, $ketuaAsesor->id, $scores, true);
        $this->assertTrue($akreditasi->is_nk_final);
        $this->assertSame('4.00', $akreditasi->nk);

        $workflow->ketuaUploadLaporan(
            $akreditasi->id,
            $ketuaAsesor->id,
            UploadedFile::fake()->create('laporan-individu.pdf', 120, 'application/pdf'),
            UploadedFile::fake()->create('laporan-kelompok.pdf', 120, 'application/pdf')
        );
        $this->assertSame(
            2,
            Document::where('akreditasi_id', $akreditasi->id)
                ->where('type', DocumentService::TYPE_LAPORAN_ASESOR)
                ->count()
        );

        $workflow->pesantrenUploadKartuKendali(
            $akreditasi->id,
            $pesantrenUser->id,
            UploadedFile::fake()->create('kartu-kendali.pdf', 120, 'application/pdf')
        );

        $akreditasi = $workflow->ketuaSubmitHasilVisitasi($akreditasi->id, $ketuaAsesor->id);
        $this->assertSame(Akreditasi::STATUS_VISITASI_RESULT_SUBMITTED, $akreditasi->status);

        $akreditasi = $workflow->adminValidasiAkhir($akreditasi->id, $admin->id, true);
        $this->assertSame(Akreditasi::STATUS_FINAL_APPROVED, $akreditasi->status);
        $this->assertSame('4.00', $akreditasi->nv);
        $this->assertSame('100.00', $akreditasi->nilai);
        $this->assertSame('A', $akreditasi->peringkat);

        $akreditasi = $workflow->adminTerbitkanSK(
            $akreditasi->id,
            $admin->id,
            'SK-001/SPM/2026',
            '2031-07-01',
            UploadedFile::fake()->create('sertifikat.pdf', 120, 'application/pdf')
        );

        $this->assertSame(Akreditasi::STATUS_COMPLETED, $akreditasi->status);
        $this->assertSame('SK-001/SPM/2026', $akreditasi->nomor_sk);
        $this->assertNotNull($akreditasi->sertifikat_path);
        Storage::disk('local')->assertExists($akreditasi->sertifikat_path);
        $this->assertDatabaseHas('documents', [
            'akreditasi_id' => $akreditasi->id,
            'type' => DocumentService::TYPE_SERTIFIKAT,
            'file_path' => $akreditasi->sertifikat_path,
        ]);
        $this->assertDatabaseCount('assessments', 2);
        $this->assertSame(3, AkreditasiEdpm::where('akreditasi_id', $akreditasi->id)->where('type', 'nv')->count());
    }

    private function createCompletePesantrenData(User $user): void
    {
        $pesantren = Pesantren::create([
            'user_id' => $user->id,
            'nama_pesantren' => 'Pesantren Test',
            'ns_pesantren' => 'NSP-001',
            'alamat' => 'Jl. Pengujian No. 1',
            'layanan_satuan_pendidikan' => ['MTs'],
            'provinsi_kode' => '32',
            'tahun_pendirian' => '2001',
        ]);

        PesantrenUnit::create([
            'pesantren_id' => $pesantren->id,
            'layanan_satuan_pendidikan' => 'MTs',
            'jumlah_rombel' => 6,
        ]);

        Ipm::create(['user_id' => $user->id, 'data' => ['santri_mukim' => 100, 'butir_1' => 'sesuai', 'butir_2' => 'sesuai', 'butir_3' => 'sesuai', 'butir_4' => 'sesuai']]);
        SdmPesantren::create(['user_id' => $user->id, 'data' => ['butirs' => ['MI' => ['ustaz_tetap_L' => 12, 'ustaz_tetap_P' => 0]]]]);
        Edpm::create(['user_id' => $user->id, 'data' => ['status' => 'lengkap']]);
    }

    /**
     * @return array<int, int>
     */
    private function scorePayload(): array
    {
        return MasterEdpmButir::query()
            ->orderBy('id')
            ->limit(3)
            ->pluck('id')
            ->mapWithKeys(fn (int $id) => [$id => 4])
            ->all();
    }
}



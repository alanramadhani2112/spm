<?php

namespace Tests\Unit\Services;

use App\Models\Akreditasi;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DocumentServiceVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_rules_filter_documents_by_role_and_asesor_scope(): void
    {
        $pesantren = User::factory()->create(['role_id' => 3]);
        $akreditasi = Akreditasi::create([
            'user_id' => $pesantren->id,
            'uuid' => (string) Str::uuid(),
            'status' => Akreditasi::STATUS_INITIAL_SUBMITTED,
        ]);

        $kartuKendali = DocumentCategory::create([
            'name' => 'Kartu Kendali',
            'code' => 'kartu_kendali',
            'visible_to_roles' => ['pesantren'],
            'is_active' => true,
        ]);
        $laporanIndividu = DocumentCategory::create([
            'name' => 'Laporan Visitasi Individu',
            'code' => 'laporan_visitasi_individu',
            'visible_to_roles' => ['asesor'],
            'asesor_scope' => 'all',
            'is_active' => true,
        ]);
        $laporanKelompok = DocumentCategory::create([
            'name' => 'Laporan Visitasi Kelompok',
            'code' => 'laporan_visitasi_kelompok',
            'visible_to_roles' => ['asesor'],
            'asesor_scope' => 'ketua',
            'is_active' => true,
        ]);

        $kk = Document::create(['akreditasi_id' => $akreditasi->id, 'category_id' => $kartuKendali->id, 'type' => DocumentService::TYPE_KARTU_KENDALI, 'file_path' => 'kk.pdf']);
        $individu = Document::create(['akreditasi_id' => $akreditasi->id, 'category_id' => $laporanIndividu->id, 'type' => DocumentService::TYPE_LAPORAN_ASESOR, 'file_path' => 'individu.pdf']);
        $kelompok = Document::create(['akreditasi_id' => $akreditasi->id, 'category_id' => $laporanKelompok->id, 'type' => DocumentService::TYPE_LAPORAN_ASESOR, 'file_path' => 'kelompok.pdf']);

        $service = new DocumentService;

        $this->assertSame(
            [$kk->id],
            $service->getVisibleDocuments($akreditasi->id, DocumentService::ROLE_PESANTREN)->pluck('id')->all()
        );
        $this->assertSame(
            [$individu->id, $kelompok->id],
            $service->getVisibleDocuments($akreditasi->id, DocumentService::ROLE_ASESOR, null, 'ketua')->pluck('id')->all()
        );
        $this->assertSame(
            [$individu->id],
            $service->getVisibleDocuments($akreditasi->id, DocumentService::ROLE_ASESOR, null, 'anggota')->pluck('id')->all()
        );
    }
}

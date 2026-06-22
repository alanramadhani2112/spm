<?php

namespace Tests\Feature\Pesantren;

use App\Models\Edpm;
use App\Models\Ipm;
use App\Models\Ipr;
use App\Models\Pesantren;
use App\Models\SdmPesantren;
use App\Models\User;
use App\Services\PesantrenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DataCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_pesantren_can_complete_required_data(): void
    {
        $user = User::factory()->create(['role_id' => 3]);

        $this->actingAs($user)
            ->get(route('pesantren.data.index'))
            ->assertOk()
            ->assertSee('Kelengkapan Data Pesantren');

        $this->actingAs($user)
            ->post(route('pesantren.data.profile'), [
                'nama_pesantren' => 'Pesantren Lengkap',
                'ns_pesantren' => 'NSP-001',
                'alamat' => 'Jl. Test No. 1',
                'provinsi_kode' => '32',
                'tahun_pendirian' => '2001',
                'layanan_satuan_pendidikan' => ['MTs'],
                'units' => [
                    ['layanan_satuan_pendidikan' => 'MTs', 'jumlah_rombel' => 6],
                ],
            ])
            ->assertRedirect(route('pesantren.data.index'));

        $this->actingAs($user)
            ->post(route('pesantren.data.ipm'), [
                'ipm' => ['santri_mukim' => 120, 'santri_non_mukim' => 30, 'butir_1' => 'sesuai', 'butir_2' => 'sesuai', 'butir_3' => 'sesuai', 'butir_4' => 'sesuai'],
            ])
            ->assertRedirect(route('pesantren.data.index'));

        $this->actingAs($user)
            ->post(route('pesantren.data.sdm'), [
                'sdm' => ['butirs' => ['MI' => ['ustaz_tetap_L' => 7, 'ustaz_tetap_P' => 7, 'tenaga_kependidikan_L' => 3, 'tenaga_kependidikan_P' => 2]]],
            ])
            ->assertRedirect(route('pesantren.data.index'));

        $this->actingAs($user)
            ->post(route('pesantren.data.edpm'), [
                'edpm' => ['self_assessment' => 'Siap mengikuti akreditasi'],
            ])
            ->assertRedirect(route('pesantren.data.index'));

        // IPR 22 butir — create langsung di DB
        $iprButirs = [];
        for ($i = 1; $i <= 22; $i++) {
            $iprButirs[$i] = ['file' => 'ipr-documents/test_butir_' . $i . '.pdf'];
        }
        Ipr::create(['user_id' => $user->id, 'data' => ['butirs' => $iprButirs]]);

        $this->assertDatabaseHas('pesantrens', ['user_id' => $user->id, 'nama_pesantren' => 'Pesantren Lengkap']);
        $this->assertSame(120, Ipm::where('user_id', $user->id)->first()->data['santri_mukim']);
        $this->assertNotNull(SdmPesantren::where('user_id', $user->id)->first()->data['butirs']);
        $this->assertNotNull(Edpm::where('user_id', $user->id)->first()->data);
        $this->assertNotNull(Ipr::where('user_id', $user->id)->first()->data);
        $this->assertTrue(app(PesantrenService::class)->checkDataCompleteness($user->id)['assessmentReady']);
    }

    public function test_profile_cannot_be_updated_when_locked(): void
    {
        $user = User::factory()->create(['role_id' => 3]);
        Pesantren::create([
            'user_id' => $user->id,
            'nama_pesantren' => 'Terkunci',
            'ns_pesantren' => 'NSP-LOCK',
            'alamat' => 'Alamat',
            'provinsi_kode' => '32',
            'tahun_pendirian' => '2001',
            'layanan_satuan_pendidikan' => ['MTs'],
            'is_locked' => true,
        ]);

        $this->actingAs($user)
            ->post(route('pesantren.data.profile'), [
                'nama_pesantren' => 'Diubah',
                'ns_pesantren' => 'NSP-001',
                'alamat' => 'Jl. Test',
                'provinsi_kode' => '32',
                'tahun_pendirian' => '2001',
                'layanan_satuan_pendidikan' => ['MTs'],
                'units' => [['layanan_satuan_pendidikan' => 'MTs', 'jumlah_rombel' => 1]],
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('pesantrens', ['user_id' => $user->id, 'nama_pesantren' => 'Terkunci']);
    }

    public function test_profile_persists_new_pesantren_fields(): void
    {
        $user = User::factory()->create(['role_id' => 3]);

        $this->actingAs($user)
            ->post(route('pesantren.data.profile'), [
                'nama_pesantren' => 'Pesantren Profil Baru',
                'ns_pesantren' => 'NSP-NEW',
                'alamat' => 'Jl. Profil No. 2',
                'provinsi_kode' => '32',
                'tahun_pendirian' => '2004',
                'email_pesantren' => 'profil@example.test',
                'nspp' => 'NSPP-7788',
                'persyarikatan' => 'Daerah',
                'luas_tanah' => '2500 m2',
                'luas_bangunan' => '1400 m2',
                'status_kepemilikan_tanah' => 'wakaf',
                'sertifikat_nsp' => 'SERT-009',
                'layanan_satuan_pendidikan' => ['MTs'],
                'units' => [
                    ['layanan_satuan_pendidikan' => 'MTs', 'jumlah_rombel' => 4],
                ],
            ])
            ->assertRedirect(route('pesantren.data.index'));

        $this->assertDatabaseHas('pesantrens', [
            'user_id' => $user->id,
            'nama_pesantren' => 'Pesantren Profil Baru',
            'email_pesantren' => 'profil@example.test',
            'nspp' => 'NSPP-7788',
            'persyarikatan' => 'Daerah',
            'luas_tanah' => '2500 m2',
            'luas_bangunan' => '1400 m2',
            'status_kepemilikan_tanah' => 'wakaf',
            'sertifikat_nsp' => 'SERT-009',
        ]);
    }

    public function test_profile_requires_at_least_one_filled_unit_row(): void
    {
        $user = User::factory()->create(['role_id' => 3]);

        $this->actingAs($user)
            ->from(route('pesantren.data.index'))
            ->post(route('pesantren.data.profile'), [
                'nama_pesantren' => 'Pesantren Unit Kosong',
                'ns_pesantren' => 'NSP-EMPTY',
                'alamat' => 'Jl. Unit No. 1',
                'provinsi_kode' => '32',
                'tahun_pendirian' => '2005',
                'layanan_satuan_pendidikan' => ['MTs'],
                'units' => [
                    ['layanan_satuan_pendidikan' => '', 'jumlah_rombel' => 0],
                ],
            ])
            ->assertRedirect(route('pesantren.data.index'))
            ->assertSessionHasErrors('units');
    }

    public function test_ipr_partial_upload_preserves_existing_files(): void
    {
        $user = User::factory()->create(['role_id' => 3]);

        Ipr::create([
            'user_id' => $user->id,
            'data' => [
                'butirs' => [
                    1 => ['file' => 'ipr-documents/existing-1.pdf'],
                    2 => ['file' => 'ipr-documents/existing-2.pdf'],
                ],
            ],
        ]);

        $this->actingAs($user)
            ->post(route('pesantren.data.ipr'), [
                'ipr' => [
                    'butirs' => [
                        2 => ['file' => UploadedFile::fake()->create('new-2.pdf', 100, 'application/pdf')],
                    ],
                ],
            ])
            ->assertRedirect(route('pesantren.data.index'));

        $ipr = Ipr::where('user_id', $user->id)->firstOrFail();

        $this->assertSame('ipr-documents/existing-1.pdf', $ipr->data['butirs'][1]['file']);
        $this->assertStringEndsWith('.pdf', $ipr->data['butirs'][2]['file']);
        $this->assertNotSame('ipr-documents/existing-2.pdf', $ipr->data['butirs'][2]['file']);
    }

    public function test_ipm_partial_update_preserves_existing_values(): void
    {
        $user = User::factory()->create(['role_id' => 3]);

        Ipm::create([
            'user_id' => $user->id,
            'data' => [
                'santri_mukim' => 100,
                'santri_non_mukim' => 20,
                'kurikulum_utama' => 'Kurikulum Lama',
                'butir_1' => 'sesuai',
                'butir_2' => 'sesuai',
                'butir_3' => 'sesuai',
                'butir_4' => 'sesuai',
            ],
        ]);

        $this->actingAs($user)
            ->post(route('pesantren.data.ipm'), [
                'ipm' => [
                    'santri_mukim' => 150,
                    'butir_1' => 'sesuai',
                    'butir_2' => 'sesuai',
                    'butir_3' => 'sesuai',
                    'butir_4' => 'sesuai',
                ],
            ])
            ->assertRedirect(route('pesantren.data.index'));

        $ipm = Ipm::where('user_id', $user->id)->firstOrFail();

        $this->assertSame(150, $ipm->data['santri_mukim']);
        $this->assertSame(20, $ipm->data['santri_non_mukim']);
        $this->assertSame('Kurikulum Lama', $ipm->data['kurikulum_utama']);
    }

    public function test_sdm_partial_update_preserves_existing_values(): void
    {
        $user = User::factory()->create(['role_id' => 3]);

        SdmPesantren::create([
            'user_id' => $user->id,
            'data' => [
                'butirs' => [
                    'MI' => [
                        'ustaz_tetap_L' => 5,
                        'ustaz_tetap_P' => 6,
                        'nbm_L' => 2,
                    ],
                ],
                'catatan_sdm' => 'Catatan awal',
            ],
        ]);

        $this->actingAs($user)
            ->post(route('pesantren.data.sdm'), [
                'sdm' => [
                    'butirs' => [
                        'MI' => [
                            'ustaz_tetap_L' => 8,
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('pesantren.data.index'));

        $sdm = SdmPesantren::where('user_id', $user->id)->firstOrFail();

        $this->assertSame(8, $sdm->data['butirs']['MI']['ustaz_tetap_L']);
        $this->assertSame(6, $sdm->data['butirs']['MI']['ustaz_tetap_P']);
        $this->assertSame(2, $sdm->data['butirs']['MI']['nbm_L']);
        $this->assertSame('Catatan awal', $sdm->data['catatan_sdm']);
    }

    public function test_edpm_partial_update_preserves_existing_values(): void
    {
        $user = User::factory()->create(['role_id' => 3]);

        Edpm::create([
            'user_id' => $user->id,
            'data' => [
                'self_assessment' => 'Ringkasan awal',
                'butirs' => [
                    10 => [
                        'self_assessment' => 'sesuai',
                        'bukti_link' => 'https://example.test/bukti-awal',
                    ],
                    11 => [
                        'self_assessment' => 'belum',
                        'bukti_link' => 'https://example.test/bukti-lain',
                    ],
                ],
            ],
        ]);

        $this->actingAs($user)
            ->post(route('pesantren.data.edpm'), [
                'edpm' => [
                    'butirs' => [
                        10 => [
                            'self_assessment' => 'perlu_perbaikan',
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('pesantren.data.index'));

        $edpm = Edpm::where('user_id', $user->id)->firstOrFail();

        $this->assertSame('Ringkasan awal', $edpm->data['self_assessment']);
        $this->assertSame('perlu_perbaikan', $edpm->data['butirs'][10]['self_assessment']);
        $this->assertSame('https://example.test/bukti-awal', $edpm->data['butirs'][10]['bukti_link']);
        $this->assertSame('belum', $edpm->data['butirs'][11]['self_assessment']);
        $this->assertSame('https://example.test/bukti-lain', $edpm->data['butirs'][11]['bukti_link']);
    }
}





<?php

namespace Tests\Feature\Pesantren;

use App\Models\Edpm;
use App\Models\Ipm;
use App\Models\Pesantren;
use App\Models\SdmPesantren;
use App\Models\User;
use App\Services\PesantrenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataCompletionTest extends TestCase
{
    use RefreshDatabase;

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
                'ipm' => ['santri_mukim' => 120, 'santri_non_mukim' => 30],
            ])
            ->assertRedirect(route('pesantren.data.index'));

        $this->actingAs($user)
            ->post(route('pesantren.data.sdm'), [
                'sdm' => ['ustaz_tetap' => 14, 'tenaga_kependidikan' => 5],
            ])
            ->assertRedirect(route('pesantren.data.index'));

        $this->actingAs($user)
            ->post(route('pesantren.data.edpm'), [
                'edpm' => ['self_assessment' => 'Siap mengikuti akreditasi', 'kesiapan_dokumen' => 'lengkap'],
            ])
            ->assertRedirect(route('pesantren.data.index'));

        $this->assertDatabaseHas('pesantrens', ['user_id' => $user->id, 'nama_pesantren' => 'Pesantren Lengkap']);
        $this->assertSame(120, Ipm::where('user_id', $user->id)->first()->data['santri_mukim']);
        $this->assertSame(14, SdmPesantren::where('user_id', $user->id)->first()->data['ustaz_tetap']);
        $this->assertSame('lengkap', Edpm::where('user_id', $user->id)->first()->data['kesiapan_dokumen']);
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
}

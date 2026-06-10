<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesantren extends Model
{
    protected $fillable = [
        'user_id',
        'nama_pesantren',
        'nspp',
        'ns_pesantren',
        'alamat',
        'kota_kabupaten',
        'kabupaten',
        'kabupaten_kode',
        'kecamatan',
        'kelurahan',
        'provinsi',
        'provinsi_kode',
        'tahun_pendirian',
        'nama_mudir',
        'jenjang_pendidikan_mudir',
        'telp_pesantren',
        'hp_wa',
        'email_pesantren',
        'persyarikatan',
        'visi',
        'misi',
        'luas_tanah',
        'luas_bangunan',
        'layanan_satuan_pendidikan',
        'status_kepemilikan_tanah',
        'sertifikat_nsp',
        'rk_anggaran',
        'silabus_rpp',
        'peraturan_kepegawaian',
        'file_lk_iapm',
        'laporan_tahunan',
        'dok_profil',
        'dok_nsp',
        'dok_renstra',
        'dok_rk_anggaran',
        'dok_kurikulum',
        'dok_silabus_rpp',
        'dok_kepengasuhan',
        'dok_peraturan_kepegawaian',
        'dok_sarpras',
        'dok_laporan_tahunan',
        'dok_sop',
        'is_locked',
    ];

    protected $casts = [
        'layanan_satuan_pendidikan' => 'array',
        'is_locked' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function units()
    {
        return $this->hasMany(PesantrenUnit::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesantrenUnit extends Model
{
    protected $fillable = [
        'pesantren_id',
        'layanan_satuan_pendidikan',
        'jumlah_rombel',
    ];

    public function pesantren()
    {
        return $this->belongsTo(Pesantren::class);
    }
}

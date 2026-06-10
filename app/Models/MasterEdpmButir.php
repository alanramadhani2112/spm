<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterEdpmButir extends Model
{
    protected $fillable = [
        'komponen_id',
        'kode',
        'name',
        'nama',
        'deskripsi',
    ];

    public function komponen(): BelongsTo
    {
        return $this->belongsTo(MasterEdpmKomponen::class, 'komponen_id');
    }
}

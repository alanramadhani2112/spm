<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterEdpmSubKomponen extends Model
{
    protected $fillable = [
        'komponen_id',
        'kode',
        'nama',
        'deskripsi',
    ];

    public function komponen(): BelongsTo
    {
        return $this->belongsTo(MasterEdpmKomponen::class, 'komponen_id');
    }

    public function butirs(): HasMany
    {
        return $this->hasMany(MasterEdpmButir::class, 'sub_komponen', 'kode');
    }
}

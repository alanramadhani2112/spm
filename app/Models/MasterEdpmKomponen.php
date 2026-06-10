<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterEdpmKomponen extends Model
{
    protected $fillable = [
        'kode',
        'name',
        'nama',
    ];

    public function butirs(): HasMany
    {
        return $this->hasMany(MasterEdpmButir::class, 'komponen_id');
    }
}

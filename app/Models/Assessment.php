<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assessment extends Model
{
    protected $fillable = [
        'akreditasi_id',
        'asesor_id',
        'tipe',
    ];

    public function akreditasi(): BelongsTo
    {
        return $this->belongsTo(Akreditasi::class);
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }
}

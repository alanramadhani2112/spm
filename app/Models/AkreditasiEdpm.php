<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AkreditasiEdpm extends Model
{
    protected $fillable = [
        'akreditasi_id',
        'asesor_id',
        'butir_id',
        'value',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
        ];
    }

    public function akreditasi(): BelongsTo
    {
        return $this->belongsTo(Akreditasi::class);
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    public function butir(): BelongsTo
    {
        return $this->belongsTo(MasterEdpmButir::class, 'butir_id');
    }
}

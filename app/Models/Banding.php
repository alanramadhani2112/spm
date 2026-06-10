<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banding extends Model
{
    protected $fillable = [
        'akreditasi_id',
        'user_id',
        'reason',
        'status',
        'admin_response',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }

    public function akreditasi(): BelongsTo
    {
        return $this->belongsTo(Akreditasi::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}

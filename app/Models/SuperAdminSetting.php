<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuperAdminSetting extends Model
{
    protected $fillable = ['key', 'value', 'description', 'updated_by'];

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

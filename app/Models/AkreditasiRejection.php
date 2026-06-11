<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AkreditasiRejection extends Model
{
    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'cycle' => 'integer',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'completion_data',
    ];

    protected $casts = [
        'completion_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

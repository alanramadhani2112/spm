<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'akreditasi_id',
        'category_id',
        'type',
        'file_path',
        'uploaded_by_user_id',
    ];

    public function akreditasi(): BelongsTo
    {
        return $this->belongsTo(Akreditasi::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaResource extends Model
{
    protected $fillable = [
        'name',
        'type',
        'url_path',
        'training_id',
        'external_url',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }
}

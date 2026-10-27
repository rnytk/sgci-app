<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaResource extends Model
{
    protected $fillable = [
        'name',
        'type',
        'training_id',
        'url_path',
        'external_url',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }


    public function getPublicUrlAttribute()
    {
        if (str_starts_with($this->url_path, 'http')) {
            return $this->url_path;
        }

        return Storage::url($this->url_path);
    }
}

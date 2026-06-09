<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    
    protected $fillable = [
        'key',
        'path',
        'type',
        'alt',
    ];

    
    protected $appends = [
        'url',
    ];

    
    public function getUrlAttribute(): ?string
    {
        return $this->path
            ? '/storage/'.ltrim($this->path, '/')
            : null;
    }
}

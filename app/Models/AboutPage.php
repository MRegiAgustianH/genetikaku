<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    
    use HasFactory;

    
    protected $fillable = [
        'title',
        'content',
        'image_path',
    ];


    protected $appends = [
        'image_url',
    ];

    
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? '/storage/'.ltrim($this->image_path, '/')
            : null;
    }
}

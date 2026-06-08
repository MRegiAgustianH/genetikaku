<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'path',
        'type',
        'alt',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'url',
    ];

    /**
     * URL publik aset media, atau null bila belum ada berkas.
     */
    public function getUrlAttribute(): ?string
    {
        return $this->path
            ? Storage::disk('public')->url($this->path)
            : null;
    }
}

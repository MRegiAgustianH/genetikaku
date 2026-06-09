<?php

namespace App\Models\Concerns;

/**
 * Accessor IMK untuk model yang memiliki kolom `illustration_path`.
 *
 * Menyediakan:
 *  - illustration_url  : URL relatif ("/storage/...") atau null.
 *  - illustration_type : 'image' | 'gif' | 'video' (diturunkan dari ekstensi).
 *
 * Model yang memakai trait ini harus menambahkan 'illustration_url' dan
 * 'illustration_type' ke $appends, serta 'illustration_path' ke $fillable.
 */
trait HasIllustration
{
    public function getIllustrationUrlAttribute(): ?string
    {
        return $this->illustration_path
            ? '/storage/'.ltrim($this->illustration_path, '/')
            : null;
    }

    public function getIllustrationTypeAttribute(): ?string
    {
        if (! $this->illustration_path) {
            return null;
        }

        $extension = strtolower(pathinfo($this->illustration_path, PATHINFO_EXTENSION));

        return match (true) {
            $extension === 'gif' => 'gif',
            in_array($extension, ['mp4', 'webm'], true) => 'video',
            default => 'image',
        };
    }
}

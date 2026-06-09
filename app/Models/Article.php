<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    /** @use HasFactory<\Database\Factories\ArticleFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'status',
        'image_path',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'image_url',
    ];

    /**
     * URL publik gambar artikel, atau null bila belum ada gambar.
     *
     * Dikembalikan sebagai URL relatif terhadap root ("/storage/...") agar
     * gambar tetap tampil pada host/port mana pun (mis. `php artisan serve`
     * di :8000) tanpa bergantung pada nilai APP_URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? '/storage/'.ltrim($this->image_path, '/')
            : null;
    }
}

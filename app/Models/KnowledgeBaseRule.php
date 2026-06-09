<?php

namespace App\Models;

use App\Models\Concerns\HasIllustration;
use Illuminate\Database\Eloquent\Model;

/**
 * Aturan Basis_Pengetahuan skrining Thalassemia (Req 12).
 *
 * Memetakan satu indikator/ciri skrining ke bobot dan kategori yang
 * diindikasikannya. Dipakai {@see \App\Services\ScreeningEngine} pada Tahap 1.
 * Setiap indikator dapat memiliki ilustrasi (IMK) yang tampil di form skrining.
 */
class KnowledgeBaseRule extends Model
{
    use HasIllustration;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'indicator',
        'weight',
        'classification_mapping',
        'illustration_path',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'illustration_url',
        'illustration_type',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight' => 'integer',
        ];
    }
}

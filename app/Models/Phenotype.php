<?php

namespace App\Models;

use App\Domain\PhenotypeCategory;
use App\Models\Concerns\HasIllustration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phenotype extends Model
{
    
    use HasFactory;
    use HasIllustration;

    
    protected $fillable = [
        'category',
        'value',
        'illustration_path',
    ];

    protected $appends = [
        'illustration_url',
        'illustration_type',
    ];

    
    protected function casts(): array
    {
        return [
            'category' => PhenotypeCategory::class,
        ];
    }
}

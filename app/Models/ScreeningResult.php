<?php

namespace App\Models;

use App\Domain\ScreeningCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ScreeningResult extends Model
{
    
    use HasFactory;

    
    protected $fillable = [
        'father_name',
        'mother_name',
        'father_result',
        'mother_result',
    ];

    
    protected function casts(): array
    {
        return [
            'father_result' => ScreeningCategory::class,
            'mother_result' => ScreeningCategory::class,
        ];
    }

    
    public function predictionResult(): HasOne
    {
        return $this->hasOne(PredictionResult::class);
    }
}

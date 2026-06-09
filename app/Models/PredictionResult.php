<?php

namespace App\Models;

use App\Domain\ThalassemiaRisk;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PredictionResult extends Model
{
    
    use HasFactory;

   
    protected $fillable = [
        'screening_result_id',
        'physical_result',
        'thalassemia_risk',
        'probabilities',
    ];

    
    protected function casts(): array
    {
        return [
            'physical_result' => 'array',
            'probabilities' => 'array',
            'thalassemia_risk' => ThalassemiaRisk::class,
        ];
    }

    public function screeningResult(): BelongsTo
    {
        return $this->belongsTo(ScreeningResult::class);
    }
}

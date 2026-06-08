<?php

namespace App\Models;

use App\Domain\ThalassemiaRisk;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PredictionResult extends Model
{
    /** @use HasFactory<\Database\Factories\PredictionResultFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'screening_result_id',
        'physical_result',
        'thalassemia_risk',
        'probabilities',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'physical_result' => 'array',
            'probabilities' => 'array',
            'thalassemia_risk' => ThalassemiaRisk::class,
        ];
    }

    /**
     * Get the screening result that owns this prediction result.
     *
     * @return BelongsTo<ScreeningResult, $this>
     */
    public function screeningResult(): BelongsTo
    {
        return $this->belongsTo(ScreeningResult::class);
    }
}

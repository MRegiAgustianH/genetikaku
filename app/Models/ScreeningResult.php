<?php

namespace App\Models;

use App\Domain\ScreeningCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ScreeningResult extends Model
{
    /** @use HasFactory<\Database\Factories\ScreeningResultFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'father_name',
        'mother_name',
        'father_result',
        'mother_result',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'father_result' => ScreeningCategory::class,
            'mother_result' => ScreeningCategory::class,
        ];
    }

    /**
     * Get the prediction result associated with this screening result.
     *
     * @return HasOne<PredictionResult, $this>
     */
    public function predictionResult(): HasOne
    {
        return $this->hasOne(PredictionResult::class);
    }
}

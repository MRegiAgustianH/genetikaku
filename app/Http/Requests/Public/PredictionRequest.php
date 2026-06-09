<?php

namespace App\Http\Requests\Public;

use App\Domain\PhenotypeCategory;
use App\Models\Phenotype;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class PredictionRequest extends FormRequest
{
  
    public const CATEGORY_FIELDS = [
        'blood' => PhenotypeCategory::GolonganDarah,
        'iris' => PhenotypeCategory::WarnaIris,
        'hair' => PhenotypeCategory::TeksturRambut,
        'ear' => PhenotypeCategory::BentukCuping,
    ];

    
    public const PARENTS = ['father', 'mother'];

    
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        $rules = [];

        foreach (self::CATEGORY_FIELDS as $suffix => $category) {
            $allowedValues = $this->allowedValuesFor($category);

            foreach (self::PARENTS as $parent) {
                $rules["{$parent}_{$suffix}"] = ['required', 'string', Rule::in($allowedValues)];
            }
        }

        return $rules;
    }

    
    public function attributes(): array
    {
        $labels = ['father' => 'ayah', 'mother' => 'ibu'];

        $attributes = [];

        foreach (self::CATEGORY_FIELDS as $suffix => $category) {
            foreach (self::PARENTS as $parent) {
                $attributes["{$parent}_{$suffix}"] = "{$category->value} {$labels[$parent]}";
            }
        }

        return $attributes;
    }

    
    private function allowedValuesFor(PhenotypeCategory $category): array
    {
        return Phenotype::query()
            ->where('category', $category->value)
            ->pluck('value')
            ->all();
    }
}

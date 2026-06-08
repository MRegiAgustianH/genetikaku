<?php

namespace Database\Factories;

use App\Domain\ScreeningCategory;
use App\Models\KnowledgeBaseRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnowledgeBaseRule>
 */
class KnowledgeBaseRuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<KnowledgeBaseRule>
     */
    protected $model = KnowledgeBaseRule::class;

    /**
     * Canonical Indikator_Skrining used by Basis_Pengetahuan (Req 1.1).
     *
     * @return list<string>
     */
    public static function indicators(): array
    {
        return [
            'Riwayat keluarga Thalassemia',
            'Riwayat diagnosis Thalassemia',
            'Riwayat anemia',
            'Kadar Hb rendah',
            'Riwayat transfusi darah',
            'Gejala pendukung lainnya',
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'indicator' => fake()->unique()->randomElement(self::indicators()),
            'weight' => fake()->numberBetween(1, 5),
            'classification_mapping' => fake()->randomElement([
                ScreeningCategory::Normal->value,
                ScreeningCategory::Carrier->value,
                ScreeningCategory::BerisikoTinggi->value,
            ]),
        ];
    }
}

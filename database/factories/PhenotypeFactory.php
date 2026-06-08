<?php

namespace Database\Factories;

use App\Domain\PhenotypeCategory;
use App\Models\Phenotype;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Phenotype>
 */
class PhenotypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Phenotype>
     */
    protected $model = Phenotype::class;

    /**
     * Canonical set of valid Data_Fenotipe values per category.
     *
     * Shared source of truth used by seeders and other factories
     * (e.g. TrainingDataFactory) so that Data_Latih attribute values
     * stay consistent with Data_Fenotipe (Req 14.x).
     *
     * @return array<string, list<string>>
     */
    public static function values(): array
    {
        return [
            PhenotypeCategory::GolonganDarah->value => ['A', 'B', 'AB', 'O'],
            PhenotypeCategory::WarnaIris->value => ['Cokelat', 'Hitam', 'Biru', 'Hijau'],
            PhenotypeCategory::TeksturRambut->value => ['Lurus', 'Bergelombang', 'Keriting'],
            PhenotypeCategory::BentukCuping->value => ['Melekat', 'Terpisah'],
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $values = self::values();
        $category = fake()->randomElement(array_keys($values));

        return [
            'category' => $category,
            'value' => fake()->randomElement($values[$category]),
        ];
    }

    /**
     * State for a specific phenotype category.
     */
    public function forCategory(PhenotypeCategory $category): static
    {
        $values = self::values();

        return $this->state(fn (array $attributes) => [
            'category' => $category->value,
            'value' => fake()->randomElement($values[$category->value]),
        ]);
    }
}

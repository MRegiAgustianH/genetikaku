<?php

namespace Database\Factories;

use App\Models\AboutPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AboutPage>
 */
class AboutPageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<AboutPage>
     */
    protected $model = AboutPage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Tentang '.fake()->company(),
            'content' => fake()->paragraphs(4, true),
        ];
    }
}

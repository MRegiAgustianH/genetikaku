<?php

namespace Database\Seeders;

use App\Models\Phenotype;
use Database\Factories\PhenotypeFactory;
use Illuminate\Database\Seeder;


class PhenotypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (PhenotypeFactory::values() as $category => $values) {
            foreach ($values as $value) {
                Phenotype::query()->firstOrCreate([
                    'category' => $category,
                    'value' => $value,
                ]);
            }
        }
    }
}

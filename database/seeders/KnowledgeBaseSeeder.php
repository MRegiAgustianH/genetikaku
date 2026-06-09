<?php

namespace Database\Seeders;

use App\Domain\ScreeningRuleSet;
use App\Http\Requests\Public\ScreeningRequest;
use App\Models\KnowledgeBaseRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seed Basis_Pengetahuan awal dari nilai hasil wawancara pakar yang
 * terdokumentasi pada {@see ScreeningRuleSet}.
 *
 * Setelah di-seed, admin dapat menyesuaikan bobot/pemetaan tiap indikator
 * (dan menambah indikator baru) melalui dashboard. Memakai updateOrCreate
 * berdasarkan indikator agar idempoten saat seeder dijalankan ulang.
 */
class KnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        // Peta label -> slug bawaan (kebalikan dari ScreeningRequest::INDICATORS)
        // agar indikator bawaan memakai slug yang konsisten/stabil.
        $slugByLabel = array_flip(ScreeningRequest::INDICATORS);

        foreach (ScreeningRuleSet::default() as $rule) {
            $slug = $slugByLabel[$rule->indicator] ?? Str::slug($rule->indicator, '_');

            KnowledgeBaseRule::query()->updateOrCreate(
                ['indicator' => $rule->indicator],
                [
                    'slug' => $slug,
                    'weight' => $rule->weight,
                    'classification_mapping' => $rule->classificationMapping,
                ],
            );
        }
    }
}

<?php

namespace App\Services;

use App\Domain\KnowledgeBaseRule;
use App\Domain\ScreeningCategory;

/**
 * Mesin_Skrining (Tahap 1).
 *
 * Memetakan jawaban Indikator_Skrining seorang orang tua ke tepat satu
 * Hasil_Skrining_Orang_Tua (`Normal`, `Carrier`, `Berisiko Tinggi`)
 * berdasarkan Basis_Pengetahuan (Req 1.2, 1.3, 12.2).
 *
 * Kelas ini MURNI: tidak menyentuh database/HTTP. Seluruh masukan diberikan
 * secara eksplisit sehingga hasilnya deterministik dan mudah diuji property.
 *
 * ## Interpretasi field `classification_mapping`
 *
 * Setiap {@see KnowledgeBaseRule} memetakan satu Indikator_Skrining ke:
 *   - `weight`               : kontribusi skor saat indikator dijawab "ya".
 *   - `classificationMapping`: label kategori yang paling diasosiasikan dengan
 *                              indikator tersebut, salah satu dari
 *                              `Normal` | `Carrier` | `Berisiko Tinggi`.
 *
 * `classificationMapping` dipakai untuk MENURUNKAN ambang batas (threshold)
 * langsung dari Basis_Pengetahuan, bukan dari konstanta hard-coded:
 *   - `highRiskThreshold` = bobot TERKECIL di antara seluruh aturan yang
 *                           dipetakan ke `Berisiko Tinggi`.
 *   - `carrierThreshold`  = bobot TERKECIL di antara seluruh aturan yang
 *                           dipetakan ke `Carrier`.
 *
 * Konsekuensinya: mengiyakan satu indikator berisiko tinggi mana pun sudah
 * cukup membuat skor mencapai `highRiskThreshold`, sehingga orang tua tersebut
 * langsung diklasifikasikan `Berisiko Tinggi` — sesuai intuisi klinis bahwa
 * indikator kuat (mis. riwayat diagnosis) bersifat menentukan.
 *
 * ## Algoritma `classify`
 *   1. `score` = jumlah `weight` untuk setiap aturan yang indikatornya dijawab
 *      secara afirmatif pada `$answers`.
 *   2. Pemetaan (high-risk diperiksa lebih dulu agar fungsi tetap total):
 *        - `score >= highRiskThreshold`  -> Berisiko Tinggi
 *        - `score >= carrierThreshold`   -> Carrier
 *        - selain itu                    -> Normal
 *
 * Fungsi selalu mengembalikan tepat satu {@see ScreeningCategory} dan
 * deterministik (idempoten) untuk masukan yang sama.
 */
final class ScreeningEngine
{
    /**
     * Klasifikasikan jawaban indikator seorang orang tua.
     *
     * @param  array<string,mixed>  $answers  Jawaban indikator, dikunci nama
     *                                         indikator. Nilai afirmatif
     *                                         (true, 1, "ya"/"yes"/"true")
     *                                         dihitung sebagai "terpenuhi".
     * @param  list<KnowledgeBaseRule>  $rules  Aturan dari Basis_Pengetahuan.
     */
    public function classify(array $answers, array $rules): ScreeningCategory
    {
        $score = 0;
        $highRiskThreshold = null;
        $carrierThreshold = null;

        foreach ($rules as $rule) {
            if ($this->isAffirmative($answers[$rule->indicator] ?? null)) {
                $score += $rule->weight;
            }

            $mapping = $this->normalizeCategory($rule->classificationMapping);

            if ($mapping === ScreeningCategory::BerisikoTinggi) {
                $highRiskThreshold = $this->minOrValue($highRiskThreshold, $rule->weight);
            } elseif ($mapping === ScreeningCategory::Carrier) {
                $carrierThreshold = $this->minOrValue($carrierThreshold, $rule->weight);
            }
        }

        if ($highRiskThreshold !== null && $score >= $highRiskThreshold) {
            return ScreeningCategory::BerisikoTinggi;
        }

        if ($carrierThreshold !== null && $score >= $carrierThreshold) {
            return ScreeningCategory::Carrier;
        }

        return ScreeningCategory::Normal;
    }

    /**
     * Tentukan apakah sebuah jawaban indikator bernilai afirmatif ("ya").
     */
    private function isAffirmative(mixed $answer): bool
    {
        if (is_bool($answer)) {
            return $answer;
        }

        if (is_int($answer)) {
            return $answer === 1;
        }

        if (is_string($answer)) {
            return in_array(
                strtolower(trim($answer)),
                ['1', 'ya', 'yes', 'true', 'y'],
                true,
            );
        }

        return false;
    }

    /**
     * Petakan string `classification_mapping` ke {@see ScreeningCategory},
     * toleran terhadap spasi/kapitalisasi. Mengembalikan null bila tidak
     * dikenali (aturan tersebut tidak berkontribusi pada threshold).
     */
    private function normalizeCategory(string $mapping): ?ScreeningCategory
    {
        $normalized = strtolower(trim($mapping));

        return match ($normalized) {
            'normal' => ScreeningCategory::Normal,
            'carrier' => ScreeningCategory::Carrier,
            'berisiko tinggi' => ScreeningCategory::BerisikoTinggi,
            default => ScreeningCategory::tryFrom($mapping),
        };
    }

    /**
     * Kembalikan nilai terkecil antara threshold berjalan dan kandidat baru.
     */
    private function minOrValue(?int $current, int $candidate): int
    {
        return $current === null ? $candidate : min($current, $candidate);
    }
}

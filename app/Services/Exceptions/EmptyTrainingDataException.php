<?php

declare(strict_types=1);

namespace App\Services\Exceptions;

use RuntimeException;

/**
 * Dilempar oleh Mesin_Naive_Bayes ketika Data_Latih kosong sehingga
 * perhitungan prediksi dibatalkan.
 *
 * @see \App\Services\NaiveBayesClassifier
 *
 * Requirements: 3.8
 */
final class EmptyTrainingDataException extends RuntimeException
{
    public static function create(): self
    {
        return new self(
            'Prediksi belum dapat dilakukan karena data latih belum tersedia.'
        );
    }
}

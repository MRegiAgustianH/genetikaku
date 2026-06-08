<?php

declare(strict_types=1);

namespace App\Services\Exceptions;

use RuntimeException;

/**
 * Dilempar oleh Mesin_Naive_Bayes ketika ada nilai atribut masukan yang
 * tidak terdaftar pada Data_Fenotipe maupun kategori Hasil_Skrining_Orang_Tua
 * yang valid.
 *
 * @see \App\Services\NaiveBayesClassifier
 *
 * Requirements: 3.1
 */
final class InvalidAttributeException extends RuntimeException
{
    public static function forValue(string $attribute, string $value): self
    {
        return new self(sprintf(
            'Nilai "%s" untuk atribut "%s" tidak terdaftar pada Data_Fenotipe atau kategori Hasil_Skrining_Orang_Tua yang valid.',
            $value,
            $attribute,
        ));
    }
}

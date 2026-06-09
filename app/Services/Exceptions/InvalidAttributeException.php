<?php

declare(strict_types=1);

namespace App\Services\Exceptions;

use RuntimeException;


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

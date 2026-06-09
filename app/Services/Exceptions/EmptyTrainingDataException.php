<?php

declare(strict_types=1);

namespace App\Services\Exceptions;

use RuntimeException;


final class EmptyTrainingDataException extends RuntimeException
{
    public static function create(): self
    {
        return new self(
            'Prediksi belum dapat dilakukan karena data latih belum tersedia.'
        );
    }
}

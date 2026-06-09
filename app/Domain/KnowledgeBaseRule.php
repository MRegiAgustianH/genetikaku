<?php

namespace App\Domain;

final readonly class KnowledgeBaseRule
{
    public function __construct(
        public string $indicator,
        public int $weight,
        public string $classificationMapping,
    ) {}

    
    public static function fromArray(array $attributes): self
    {
        return new self(
            indicator: (string) $attributes['indicator'],
            weight: (int) $attributes['weight'],
            classificationMapping: (string) $attributes['classification_mapping'],
        );
    }
}

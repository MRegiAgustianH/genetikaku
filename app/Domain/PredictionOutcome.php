<?php

namespace App\Domain;


final readonly class PredictionOutcome
{
    /**
     * @param  array<string,string>  $physical
     * @param  array<string,array<string,float>>  $probabilities
     */
    public function __construct(
        public array $physical,
        public ThalassemiaRisk $thalassemiaRisk,
        public array $probabilities,
    ) {}
}

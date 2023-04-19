<?php

declare(strict_types=1);


namespace App\DTO;


class CurrencyRatesByDateResponseDTO
{
    public readonly float $rate;

    public readonly float $rateDifference;

    public function __construct(
        float $rate,
        float $rateDifference
    ) {
        $this->rate = round($rate, 4);
        $this->rateDifference = round($rateDifference, 4);
    }
}
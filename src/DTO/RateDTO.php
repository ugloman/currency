<?php

declare(strict_types=1);


namespace App\DTO;


use DateTime;

class RateDTO
{
    public readonly float $rate;

    public readonly DateTime $cbrDate;

    public function __construct(
        float    $rate,
        DateTime $cbrDate
    ) {
        $this->rate = $rate;
        $this->cbrDate = $cbrDate;
    }
}

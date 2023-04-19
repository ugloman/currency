<?php

declare(strict_types=1);


namespace App\DTO;

use App\Service\CurrencyRates;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

class CurrencyRatesByDateDTO
{
    public readonly DateTime $date;

    #[Assert\Length(
        exactly: 3,
        exactMessage: 'currencyCode должен состоять из 3 символов'
    )]
    public readonly string $currencyCode;

    #[Assert\Length(
        exactly: 3,
        exactMessage: 'baseCurrencyCode должен состоять из 3 символов'
    )]
    public readonly string $baseCurrencyCode;

    public function __construct(
        DateTime $date,
        string   $currencyCode,
        ?string  $baseCurrencyCode
    ) {
        $this->date = $date;
        $this->currencyCode = mb_strtoupper($currencyCode);
        $this->baseCurrencyCode = mb_strtoupper($baseCurrencyCode ?: CurrencyRates::DEFAULT_CODE_BASE_CURRENCY);
    }

}
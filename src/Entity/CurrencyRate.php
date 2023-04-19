<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CurrencyRateRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity(repositoryClass: CurrencyRateRepository::class)]
#[UniqueConstraint(name: "uniq_currency_date", columns: ["date_request", "code"])]
class CurrencyRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(
        type: 'integer'
    )]
    public readonly int $id;

    #[ORM\Column(
        type: 'string',
        length: 3,
        nullable: false
    )]
    public readonly string $code;

    #[ORM\Column(
        type: 'float',
        nullable: false
    )]
    public readonly float $rate;

    #[ORM\Column(
        type: 'date',
        nullable: false
    )]
    public readonly DateTime $dateRequest;

    #[ORM\Column(
        type: 'date',
        nullable: false
    )]
    public readonly DateTime $dateCbr;

    public static function create(
        string   $code,
        float    $rate,
        DateTime $dateRequest,
        DateTime $dateCbr
    ): self {
        $self = new self();

        $self->code = $code;
        $self->rate = $rate;
        $self->dateRequest = $dateRequest;
        $self->dateCbr = $dateCbr;

        return $self;
    }
}

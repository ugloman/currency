<?php

declare(strict_types=1);


namespace App\DTO;


use App\Infrastructure\Resolver\RequestDTOInterface;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

class CurrencyRatesByDateRequestDTO implements RequestDTOInterface
{
    #[Assert\NotBlank(message: 'Необходимо заполнить атрибут date')]
    public readonly ?DateTime $date;

    #[Assert\Length(
        exactly: 3,
        exactMessage: 'baseCurrencyCode должен состоять из 3 символов'
    )]
    public readonly ?string $baseCurrencyCode;

    #[Assert\NotBlank(message: 'Необходимо заполнить атрибут currencyCode')]
    #[Assert\Length(
        exactly: 3,
        exactMessage: 'currencyCode должен состоять из 3 символов'
    )]
    public readonly ?string $currencyCode;

    public function __construct(Request $request)
    {
        $this->baseCurrencyCode = $request->query->get('baseCurrencyCode');
        $this->currencyCode = $request->query->get('currencyCode');

        try {
            $this->date = new DateTime($request->query->get('date'));
        } catch (Throwable) {
            $this->date = null;
        }
    }

}
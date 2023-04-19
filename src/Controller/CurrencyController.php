<?php

declare(strict_types=1);


namespace App\Controller;


use App\DTO\CurrencyRatesByDateDTO;
use App\DTO\CurrencyRatesByDateRequestDTO;
use App\Service\CurrencyRates;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CurrencyController extends AbstractController
{
    public function __construct(
        private readonly CurrencyRates $getCurrencyRates
    ) {
    }

    #[Route("/get_currency_rates_by_date", methods: ["GET"])]
    public function getCurrencyRatesByDate(CurrencyRatesByDateRequestDTO $currencyRatesByDateDTO): JsonResponse
    {
        try {
            $result = $this->getCurrencyRates->getResultCurrencyRatesByDate(new CurrencyRatesByDateDTO(
                $currencyRatesByDateDTO->date,
                $currencyRatesByDateDTO->currencyCode,
                $currencyRatesByDateDTO->baseCurrencyCode
            ));
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getCode());
        }

        return new JsonResponse($result, Response::HTTP_OK);
    }

}
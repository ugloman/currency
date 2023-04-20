<?php

declare(strict_types=1);


namespace App\Service;


use App\Entity\CurrencyRate;
use App\Repository\CurrencyRateRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CurrencyRateSaving
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CurrencyRateRepository $currencyRateRepository
    ) {
    }

    /**
     * @throws Exception
     */
    public function saveFromConsumer(array $message): void
    {
        $this->validateMessage($message);

        $currencyRatesXml = $message['xml'];
        $dateRequest = new DateTime($message['dateRequest']);
        $dateRequest = min($dateRequest, new DateTime());
        $dateCbr = new DateTime($currencyRatesXml['@attributes']['Date']);
        foreach ($currencyRatesXml['Valute'] as $valute) {
            $code = $valute['CharCode'];

            $currencyRate = $this->currencyRateRepository->findOneBy([
                'code' => $code,
                'dateRequest' => $dateRequest,
                'dateCbr' => $dateCbr
            ]);
            if ($currencyRate !== null) {
                continue;
            }

            $rate = (float)(str_replace(',', '.', $valute['Value']) / (int)$valute['Nominal']);
            $currencyRate = CurrencyRate::create(
                $code,
                $rate,
                $dateRequest,
                $dateCbr
            );

            $this->em->persist($currencyRate);
        }

        $this->em->flush();
    }

    /**
     * @throws Exception
     */
    private function validateMessage(array $message): void
    {
        if (!isset($message['xml'])) {
            throw new Exception('Неверный формат сообщения');
        }

        if (!isset($message['dateRequest'])) {
            throw new Exception('Неверный формат сообщения');
        }
    }
}

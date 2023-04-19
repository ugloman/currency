<?php

declare(strict_types=1);


namespace App\Service;


use App\DTO\CurrencyRatesByDateDTO;
use App\DTO\CurrencyRatesByDateResponseDTO;
use App\DTO\RateDataDTO;
use App\Repository\CurrencyRateRepository;
use DateInterval;
use DateTime;
use Exception;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;

class CurrencyRates
{
    public const DEFAULT_CODE_BASE_CURRENCY = 'RUR';

    public function __construct(
        private readonly CurrencyRateRepository $currencyRateRepository,
        private readonly CbrService             $cbrService,
        private readonly CacheInterface         $cache
    ) {
    }

    /**
     * @throws \Exception|\Psr\Cache\InvalidArgumentException
     */
    public function getResultCurrencyRatesByDate(CurrencyRatesByDateDTO $ratesByDateDTO): CurrencyRatesByDateResponseDTO
    {
//        $cacheResponse = $this->cache->getItem(md5(
//                $ratesByDateDTO->date->format('d.m.Y') .
//                $ratesByDateDTO->currencyCode .
//                $ratesByDateDTO->baseCurrencyCode
//            ));
//        if ($cacheResponse->isHit()) {
//            return $cacheResponse->get();
//        }

        $result = $this->getCurrencyRatesByDate($ratesByDateDTO);

//        $cacheResponse->set($result);
//        $cacheResponse->expiresAfter(DateInterval::createFromDateString('1 hour'));
//        $this->cache->save($cacheResponse);
//        $this->cache->commit();

        return $result;
    }

    /**
     * @throws \Exception
     */
    private function getCurrencyRatesByDate(CurrencyRatesByDateDTO $ratesByDateDTO): CurrencyRatesByDateResponseDTO
    {
        $rateDataDTO = $this->getCurrencyRate($ratesByDateDTO->date, $ratesByDateDTO->currencyCode);
        $rate = $rateDataDTO->rate;

        $previousDayCbr = $rateDataDTO->cbrDate->modify('-1 day');
        $ratePreviousDayDataDTO = $this->getCurrencyRate($previousDayCbr, $ratesByDateDTO->currencyCode);
        $ratePreviousDay = $ratePreviousDayDataDTO->rate;

        if ($ratesByDateDTO->baseCurrencyCode !== self::DEFAULT_CODE_BASE_CURRENCY) {
            $baseRateDataDTO = $this->getCurrencyRate($ratesByDateDTO->date, $ratesByDateDTO->baseCurrencyCode);
            $baseRatePreviousDayDataDTO = $this->getCurrencyRate($previousDayCbr, $ratesByDateDTO->baseCurrencyCode);

            $rate /= $baseRateDataDTO->rate;
            $ratePreviousDay /= $baseRatePreviousDayDataDTO->rate;
        }

        return new CurrencyRatesByDateResponseDTO($rate, $rate - $ratePreviousDay);
    }

    /**
     * @throws \Exception
     */
    private function getCurrencyRate(DateTime $date, string $currencyCode): RateDataDTO
    {
        $currencyRate = $this->currencyRateRepository->findOneBy([
            'code' => $currencyCode,
            'dateRequest' => $date
        ]);

        if ($currencyRate !== null) {
            return new RateDataDTO(
                $currencyRate->rate,
                $currencyRate->dateCbr
            );
        }

        $cbrXml = $this->cbrService->getXmlCurrencyRatesByDate($date);
        $rateXml = $this->getRateXmlByCurrencyCode($cbrXml, $currencyCode);

        $currencyRateXmlValue = (float)str_replace(',', '.', (string)$rateXml->Value);
        $currencyRateXmlNominal = (int)$rateXml->Nominal;

        return new RateDataDTO(
            $currencyRateXmlValue / $currencyRateXmlNominal,
            new DateTime((string)$cbrXml['Date'])
        );
    }

    /**
     * @throws Exception
     */
    private function getRateXmlByCurrencyCode(SimpleXMLElement $cbrXml, string $currencyCode): SimpleXMLElement
    {
        $currencyRateXml = $cbrXml->xpath("//Valute[CharCode='{$currencyCode}']");
        if ($currencyRateXml === null || $currencyRateXml === false) {
            throw new Exception(
                sprintf('В цбр на эту дату нет валюты %s', $currencyCode),
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!isset($currencyRateXml[0])) {
            throw new Exception(
                sprintf('В цбр на эту дату нет валюты %s', $currencyCode),
                Response::HTTP_BAD_REQUEST
            );
        }

        return $currencyRateXml[0];
    }

}
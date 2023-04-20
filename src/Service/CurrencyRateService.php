<?php

declare(strict_types=1);


namespace App\Service;


use App\DTO\CurrencyRatesByDateDTO;
use App\DTO\CurrencyRatesByDateResponseDTO;
use App\DTO\RateDTO;
use App\Repository\CurrencyRateRepository;
use DateInterval;
use DateTime;
use Exception;
use Psr\Cache\InvalidArgumentException;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;

class CurrencyRateService
{
    public const DEFAULT_CODE_BASE_CURRENCY = 'RUR';

    public function __construct(
        private readonly CurrencyRateRepository $currencyRateRepository,
        private readonly CbrService             $cbrService,
        private readonly CacheInterface         $cache
    ) {
    }

    /**
     * @throws Exception|InvalidArgumentException
     */
    public function getCurrencyRatesByDate(CurrencyRatesByDateDTO $ratesByDateDTO): CurrencyRatesByDateResponseDTO
    {
        $cacheResponse = $this->cache->getItem(md5(
            $ratesByDateDTO->date->format('d.m.Y')
            . $ratesByDateDTO->currencyCode
            . $ratesByDateDTO->baseCurrencyCode
        ));
        if ($cacheResponse->isHit()) {
            return $cacheResponse->get();
        }

        $rateResponseDTO = $this->getRateResponseDTO($ratesByDateDTO);

        $cacheResponse->set($rateResponseDTO);
        $cacheResponse->expiresAfter(DateInterval::createFromDateString('1 hour'));
        $this->cache->save($cacheResponse);
        $this->cache->commit();

        return $rateResponseDTO;
    }

    /**
     * @throws Exception
     */
    private function getRateResponseDTO(CurrencyRatesByDateDTO $ratesByDateDTO): CurrencyRatesByDateResponseDTO
    {
        $rateDTO = $this->getCbrCurrencyRate($ratesByDateDTO->date, $ratesByDateDTO->currencyCode);
        $rate = $rateDTO->rate;

        $previousDayCbr = $rateDTO->cbrDate->modify('-1 day');
        $rateDTOByPreviousDay = $this->getCbrCurrencyRate($previousDayCbr, $ratesByDateDTO->currencyCode);
        $ratePreviousDay = $rateDTOByPreviousDay->rate;

        if ($ratesByDateDTO->baseCurrencyCode !== self::DEFAULT_CODE_BASE_CURRENCY) {
            $baseRateDTO = $this->getCbrCurrencyRate($ratesByDateDTO->date, $ratesByDateDTO->baseCurrencyCode);
            $baseRateDTOByPreviousDay = $this->getCbrCurrencyRate($previousDayCbr, $ratesByDateDTO->baseCurrencyCode);

            $rate /= $baseRateDTO->rate;
            $ratePreviousDay /= $baseRateDTOByPreviousDay->rate;
        }

        return new CurrencyRatesByDateResponseDTO($rate, $rate - $ratePreviousDay);
    }

    /**
     * @throws Exception
     */
    private function getCbrCurrencyRate(DateTime $date, string $currencyCode): RateDTO
    {
        $currencyRate = $this->currencyRateRepository->findOneBy([
            'code' => $currencyCode,
            'dateRequest' => $date
        ]);

        if ($currencyRate !== null) {
            return new RateDTO(
                $currencyRate->rate,
                $currencyRate->dateCbr
            );
        }

        $cbrXml = $this->cbrService->getXmlCurrencyRatesByDate($date);
        $rateXml = $this->getRateXmlByCurrencyCode($cbrXml, $currencyCode);

        $currencyRateXmlValue = (float)str_replace(',', '.', (string)$rateXml->Value);
        $currencyRateXmlNominal = (int)$rateXml->Nominal;

        return new RateDTO(
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
                sprintf('На cbr.ru нет валюты %s на указанную дату', $currencyCode),
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!isset($currencyRateXml[0])) {
            throw new Exception(
                sprintf('На cbr.ru нет валюты %s на указанную дату', $currencyCode),
                Response::HTTP_BAD_REQUEST
            );
        }

        return $currencyRateXml[0];
    }
}

<?php

declare(strict_types=1);


namespace App\Service;


use Curl\Curl;
use DateTime;
use DOMDocument;
use Exception;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\Response;

class CbrService
{
    private const CBR_XML_URL = 'https://www.cbr.ru/scripts/XML_daily.asp';

    private const XSD_FILE_NAME = __DIR__ . '/../../public/xsdSchema/cbrDailyCurrency.xsd';

    public function __construct(
        private readonly ProducerInterface $producer
    ) {
    }

    /**
     * @throws Exception
     */
    public function getXmlCurrencyRatesByDate(DateTime $date): SimpleXMLElement
    {
        $curl = new Curl();
        $xml = $curl->get(self::CBR_XML_URL, ['date_req' => $date->format('d.m.Y')]);
        if ($curl->error) {
            throw new Exception($curl->errorMessage, $curl->httpStatusCode);
        }

        $this->validateXsd($xml);

        $this->producer->publish(
            json_encode([
                'xml' => $xml,
                'dateRequest' => $date->format('d.m.Y')
            ]),
            'xml_currency_rates'
        );

        return $xml;
    }

    /**
     * @throws Exception
     */
    private function validateXsd(SimpleXMLElement $xml): void
    {
        $xmlString = $xml->asXML();

        $dom = new DOMDocument();

        libxml_use_internal_errors(true);

        $dom->loadXML($xmlString);

        if (!$dom->schemaValidate(self::XSD_FILE_NAME)) {
            $errors = array_map(static fn ($x) => $x->message, libxml_get_errors());
            $errorMessage = implode(";", $errors);
            libxml_clear_errors();

            throw new Exception($errorMessage, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

declare(strict_types=1);


namespace App\Consumer;


use App\Service\CurrencyRateSaving;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class XmlCurrencyRatesConsumer implements ConsumerInterface
{
    public function __construct(
        private readonly CurrencyRateSaving     $currencyRateSaving,
        private readonly LoggerInterface        $logger,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function execute(AMQPMessage $msg): void
    {
       $currencyRates = json_decode($msg->body, true);

        try {
            $this->currencyRateSaving->saveFromConsumer($currencyRates);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->em->clear();
    }
}

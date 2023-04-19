<?php

declare(strict_types=1);


namespace App\Consumer;


use App\Service\CurrencyRateSaver;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class XmlCurrencyRatesConsumer implements ConsumerInterface
{
    public function __construct(
        private readonly CurrencyRateSaver      $currencyRateSaver,
        private readonly LoggerInterface        $logger,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function execute(AMQPMessage $msg): void
    {
       $currencyRates = json_decode($msg->body,true);

        try {
            $this->currencyRateSaver->saveFromConsumer($currencyRates);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->em->clear();
    }

}
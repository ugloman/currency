<?php

declare(strict_types=1);

namespace App\Infrastructure\EventListener;

use App\Infrastructure\Exception\DTOBadException;
use PhpAmqpLib\Exception\AMQPExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class CustomBadExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof DTOBadException) {
            $messageString = trim($exception->getMessage());
            $messageArray = preg_split('~\r\n|\r|\n~', $messageString);
            $data = ['errors' => $messageArray];
            $jsonResponse = new JsonResponse($data, $exception->getCode());

            $event->setResponse($jsonResponse);
        }
    }
}

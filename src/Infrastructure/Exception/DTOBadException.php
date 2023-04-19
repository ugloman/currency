<?php

declare(strict_types=1);

namespace App\Infrastructure\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class DTOBadException extends BadRequestHttpException implements HttpExceptionInterface
{
    private array $headers;

    public function __construct(
        string     $message = null,
        \Throwable $previous = null,
        int        $code = 400,
        array      $headers = []
    ) {
        $this->headers = $headers;
        $message = preg_replace('~Object\(.*?\)\.\w+:\s*~si', '', $message);
        $message = preg_replace('~\s*\(code.*?\)~si', '', $message);
        parent::__construct($message, $previous, $code, $headers);
    }

    public function getStatusCode(): int
    {
        return 400;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

}

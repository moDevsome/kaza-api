<?php

namespace Api\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class BusinessException extends HttpException
{
    public function __construct(
        public readonly int $statusCode,
        string $message,
    ) {
        parent::__construct($statusCode, $message);
    }
}

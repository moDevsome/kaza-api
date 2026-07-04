<?php

namespace Api\Service\Technical;

use Api\Object\Technical\ResponseBufferObject;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Store datas to send in the JSON response
 */
final class ResponseBuffer
{

    private array $errors = [];
    private array $warnings = [];

    private int $statusCode = 200;

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    public function buildResponse(array|object|null $datas): JsonResponse
    {

        $jsonResponse = new JsonResponse();
        $jsonResponse->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        $jsonResponse->setCharset('UTF-8');
        $jsonResponse->setStatusCode($this->statusCode);
        $jsonResponse->setData(new ResponseBufferObject($datas, $this->errors, $this->warnings));
        return $jsonResponse;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }
}

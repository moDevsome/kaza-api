<?php

namespace Api\Service\Technical;

use Api\Helper\CorsHelper;
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
    private array $headers = array();

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    public function addHeader(string $headerName, string $headerValue): void
    {

        $this->headers[$headerName] = $headerValue;
    }

    public function buildResponse(array|object|null $datas): JsonResponse
    {
        foreach (CorsHelper::getHeaders() as $headerName => $headerValue)
            $this->addHeader($headerName, $headerValue);

        $jsonResponse = new JsonResponse(new ResponseBufferObject($datas, $this->errors, $this->warnings), $this->statusCode, $this->headers);
        $jsonResponse->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        $jsonResponse->setCharset('UTF-8');
        return $jsonResponse;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }
}

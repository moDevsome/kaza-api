<?php

namespace Api\Controller\Technical;

use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Api\Object\Technical\ResponseBufferObject;

#[AsController]
class ErrorController
{

    public function show(FlattenException $exception)
    {

        $errorText = $exception->getClass() === 'Api\Exception\BusinessException' ? $exception->getMessage() : $exception->getStatusText();

        return new JsonResponse(new ResponseBufferObject(null, [implode(' - ', [$exception->getStatusCode(), $errorText])], []));
    }
}

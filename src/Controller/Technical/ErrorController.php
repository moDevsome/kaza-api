<?php

namespace Api\Controller\Technical;

use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Api\Object\Technical\ResponseBufferObject;

#[AsController]
class ErrorController
{
    public function show(FlattenException $exception)
    {

        // --- Watching for specific exception ---
        $exceptionPath = explode('\\', $exception->getClass());
        $exceptionName = count($exceptionPath) > 0 ? array_pop($exceptionPath) : null;

        // Serializer exception
        if (in_array($exceptionName, ['NotEncodableValueException', 'MissingConstructorArgumentsException', 'NotNormalizableValueException']))
            return new JsonResponse(new ResponseBufferObject(null, [implode(' - ', [400, $exception->getMessage()])], []), 400);

        $errorText = $exception->getClass() === 'Api\Exception\BusinessException' ? $exception->getMessage() : $exception->getStatusText();

        return new JsonResponse(new ResponseBufferObject(null, [implode(' - ', [$exception->getStatusCode(), $errorText])], []));
    }

    /**
     * Avoid returning Symfony default page
     */
    #[Route('/', name: 'api_root')]
    public function rootError()
    {
        return new JsonResponse(new ResponseBufferObject(null, [implode(' - ', [404, 'Not Found'])], []));
    }
}

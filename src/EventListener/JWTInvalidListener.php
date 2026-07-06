<?php

namespace Api\EventListener;

use Api\Object\Technical\ResponseBufferObject;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;

final class JWTInvalidListener
{
    #[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_invalid')]
    public function onLexikJwtAuthenticationOnJwtInvalid(JWTInvalidEvent $event): void
    {
        $statusCode = $event->getResponse()->getStatusCode();
        $response = new ResponseBufferObject(null, [implode(' - ', [$statusCode, $event->getException()->getMessage()])], []);
        $event->setResponse(new JsonResponse($response, $statusCode));
    }
}

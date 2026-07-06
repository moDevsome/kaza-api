<?php

namespace Api\EventListener;

use Api\Object\Technical\ResponseBufferObject;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;

final class JWTNotFoundListener
{
    #[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_not_found')]
    public function onLexikJwtAuthenticationOnJwtNotFound(JWTNotFoundEvent $event): void
    {
        $statusCode = $event->getResponse()->getStatusCode();
        $response = new ResponseBufferObject(null, [implode(' - ', [$statusCode, $event->getException()->getMessage()])], []);
        $event->setResponse(new JsonResponse($response, $statusCode));
    }
}

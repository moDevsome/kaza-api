<?php

namespace Api\EventListener;

use Api\Object\Technical\ResponseBufferObject;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;

final class JWTExpiredListener
{
    #[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_expired')]
    public function onLexikJwtAuthenticationOnJwtExpired(JWTExpiredEvent $event): void
    {

        $statusCode = $event->getResponse()->getStatusCode();
        $response = new ResponseBufferObject(null, [implode(' - ', [$statusCode, "Expired token"])], []);
        $event->setResponse(new JsonResponse($response, $statusCode));
    }
}

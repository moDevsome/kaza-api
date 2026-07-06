<?php

namespace Api\EventListener;

use Api\Object\Technical\ResponseBufferObject;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

final class AuthenticationFailureListener
{
    #[AsEventListener(event: 'lexik_jwt_authentication.on_authentication_failure')]
    public function onLexikJwtAuthenticationOnAuthenticationFailure(AuthenticationFailureEvent $event): void
    {

        $statusCode = $event->getResponse()->getStatusCode();
        $response = new ResponseBufferObject(null, [implode(' - ', [$statusCode, $event->getException()->getMessage()])], []);
        $event->setResponse(new JsonResponse($response, $statusCode));
    }
}

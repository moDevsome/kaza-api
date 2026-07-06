<?php

namespace Api\EventListener;

use Api\Object\Technical\ResponseBufferObject;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

final class AuthenticationSuccessListener
{
    #[AsEventListener(event: 'lexik_jwt_authentication.on_authentication_success')]
    public function onLexikJwtAuthenticationOnAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {

        $event->setData(get_object_vars(new ResponseBufferObject($event->getData(), [], [])));
    }
}

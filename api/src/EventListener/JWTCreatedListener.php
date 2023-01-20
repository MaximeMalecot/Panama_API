<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;


class JWTCreatedListener
{
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $event->getUser();

        $payload = $event->getData();
        $payload['userId'] = $user->getId();
        $payload['isVerified'] = $user->getIsVerified();
        if(in_array('ROLE_FREELANCER', $user->getRoles())){
            $payload['isInfoVerified'] = $user->getFreelancerInfo()->getIsVerified();
        }

        $event->setData($payload);
    }
}

?>
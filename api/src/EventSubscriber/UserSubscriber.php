<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserSubscriber implements EventSubscriberInterface
{

    public function __construct(private UserPasswordHasherInterface $encoder){}

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['hashPwd', EventPriorities::PRE_WRITE],
        ];
    }

    public function hashPwd(ViewEvent $event): void
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        if(!$user instanceof User || (Request::METHOD_POST !== $method && Request::METHOD_PATCH !== $method)){
            return;
        }
        if($user->getOldPassword()){
            if(!$this->encoder->isPasswordValid($user, $user->getOldPassword())){
                throw new \Exception('Old password is not valid');
            }
        }
        if($user->getPlainPassword()){
            $user->setPassword($this->encoder->hashPassword($user, $user->getPlainPassword()));
        }
    }
}
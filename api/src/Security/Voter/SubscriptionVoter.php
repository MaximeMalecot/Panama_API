<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use \App\Entity\Subscription;

class SubscriptionVoter extends Voter
{
    public const CAN_CANCEL_SUBSCRIPTION = 'CAN_CANCEL_SUBSCRIPTION';

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::CAN_CANCEL_SUBSCRIPTION]);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        
        switch ($attribute) {
            case self::CAN_CANCEL_SUBSCRIPTION:
                return in_array("ROLE_FREELANCER_PREMIUM", $user->getRoles()) && $user->getSubscription()->getIsActive() === true ;
                break;
        }

        return false;
    }
}

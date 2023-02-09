<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FreelancerInfoVoter extends Voter
{
    public const FREELANCER_VERIFY = 'FREELANCER_VERIFY';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::FREELANCER_VERIFY]);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::FREELANCER_VERIFY:
                return $this->canVerifyFreelancer($user);
                break;
        }

        return false;
    }


    private function canVerifyFreelancer(User $user): bool
    {
        if( !$user->getIsVerified() || !in_array('ROLE_FREELANCER', $user->getRoles())){
            return false;
        }
        $subject = $user->getFreelancerInfo();
        if($subject->getIsVerified() || is_null($subject->getPhoneNb()) || is_null($subject->getAddress()) || is_null($subject->getCity())){
            return false;
        }
        return true;
    }
}

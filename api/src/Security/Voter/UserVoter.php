<?php

namespace App\Security\Voter;

use DateInterval;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    public const DIRECTOR_NEW = 'DIRECTOR_NEW';
    public const DIRECTOR_OLD = 'DIRECTOR_OLD';
    public const CHANGE_PWD = 'CHANGE_PWD';

    public function __construct(private RequestStack $requestStack, private EntityManagerInterface $em){}

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::DIRECTOR_OLD, self::DIRECTOR_NEW])
            && $subject instanceof \App\Entity\User;
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
            case self::DIRECTOR_NEW:
                return $this->isStagiaire($user);
                break;
            case self::DIRECTOR_OLD:
                return $this->isSenior($user);
                break;
        }

        return false;
    }

    private function isStagiaire(User $user){
        if(!in_array("ROLE_DIRECTOR", $user->getRoles())){
            return false;
        }

        $minDate = new DateTimeImmutable("now");
        $minDate = $minDate->sub(new DateInterval('P30D'));

        return $user->getCreatedAt() > $minDate;
    }

    private function isSenior(User $user){
        if(!in_array("ROLE_DIRECTOR", $user->getRoles())){
            return false;
        }

        $minDate = new DateTimeImmutable("now");
        $minDate = $minDate->sub(new DateInterval('P30D'));

        return $user->getCreatedAt() < $minDate;
    }
}

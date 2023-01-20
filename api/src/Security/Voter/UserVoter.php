<?php

namespace App\Security\Voter;

use DateInterval;
use App\Entity\User;
use DateTimeImmutable;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    public const GET_CLIENT = 'GET_CLIENT';

    public function __construct(private RequestStack $requestStack, private EntityManagerInterface $em, private ProjectRepository $projectRepository){}

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::GET_CLIENT])
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
            case self::GET_CLIENT:
                return $this->hasAccess($user, $subject);
                break;
        }

        return false;
    }


    private function hasAccess(User $user, User $subject){
        if( in_array("ROLE_ADMIN", $user->getRoles()) ) return true;
        if( $subject->getId() === $user->getId() )      return true;
        
        if( in_array('ROLE_CLIENT', $subject->getRoles()) 
            && in_array('ROLE_FREELANCER_PREMIUM', $user->getRoles()) 
        ){
            return $this->projectRepository->hasCommonProject($subject, $user );
        }
        return false;
    }
}

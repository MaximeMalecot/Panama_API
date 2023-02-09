<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Project;
use \App\Entity\Subscription;
use App\Repository\PropositionRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ProjectVoter extends Voter
{
    public const CREATE_PROPOSITION = 'CREATE_PROPOSITION';

    public function __construct(private PropositionRepository $propositionRepository){}

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [ self::CREATE_PROPOSITION])
        && $subject instanceof Project;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        
        switch ($attribute) {
            case self::CREATE_PROPOSITION:
                return $this->canCreateProposition($user, $subject);
        }

        return false;
    }

    public function canCreateProposition(User $user, Project $project){
        if( in_array("ROLE_ADMIN", $user->getRoles()) ) return true;
        if( in_array("ROLE_FREELANCER_PREMIUM", $user->getRoles()) ) 
            return $project->getStatus() === "ACTIVE" && $this->propositionRepository->findOneBy(['project' => $project, 'freelancer' => $user]) === null;
        return false;
    }
}

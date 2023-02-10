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
    public const DELETE_PROJECT = 'DELETE_PROJECT';

    public function __construct(private PropositionRepository $propositionRepository){}

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [ self::CREATE_PROPOSITION, self::DELETE_PROJECT])
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
            case self::DELETE_PROJECT:
                return $this->canDeleteProject($user, $subject);
        }

        return false;
    }

    public function canCreateProposition(User $user, Project $project){
        if( in_array("ROLE_ADMIN", $user->getRoles()) ) return true;
        if( in_array("ROLE_FREELANCER_PREMIUM", $user->getRoles()) ) 
            return $project->getStatus() === "ACTIVE" && $this->propositionRepository->findOneBy(['project' => $project, 'freelancer' => $user]) === null;
        return false;
    }

    public function canDeleteProject(User $user, Project $project){
        if($project->getStatus() === "IN_PROGRESS") return false;
        if( in_array("ROLE_ADMIN", $user->getRoles()) ) return true;
        return $project->getOwner() === $user;
    }
}

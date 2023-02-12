<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Project;
use \App\Entity\Subscription;
use App\Repository\ProjectRepository;
use App\Repository\PropositionRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ProjectVoter extends Voter
{
    public const CREATE_PROPOSITION = 'CREATE_PROPOSITION';
    public const DELETE_PROJECT = 'DELETE_PROJECT';
    public const SEE_FULL_PROJECT = 'SEE_FULL_PROJECT';

    public function __construct(private PropositionRepository $propositionRepository, private ProjectRepository $projectRepository){}

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [ self::CREATE_PROPOSITION, self::DELETE_PROJECT, self::SEE_FULL_PROJECT])
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
            case self::SEE_FULL_PROJECT:
                return $this->canSeeFullProject($user, $subject);
            case self::CREATE_PROPOSITION:
                return $this->canCreateProposition($user, $subject);
            case self::DELETE_PROJECT:
                return $this->canDeleteProject($user, $subject);
        }

        return false;
    }

    private function canCreateProposition(User $user, Project $project){
        if( in_array("ROLE_ADMIN", $user->getRoles()) ) return true;
        if( in_array("ROLE_FREELANCER_PREMIUM", $user->getRoles()) ) 
            return $project->getStatus() === "ACTIVE" && $this->propositionRepository->findOneBy(['project' => $project, 'freelancer' => $user]) === null;
        return false;
    }

    private function canDeleteProject(User $user, Project $project){
        if($project->getStatus() !== "ACTIVE" && $project->getStatus() !== "CREATED") return false;
        if( in_array("ROLE_ADMIN", $user->getRoles()) ) return true;
        return $project->getOwner() === $user;
    }

    private function canSeeFullProject(User $user, Project $project){
        if( in_array("ROLE_ADMIN", $user->getRoles()) ) return true;
        if( in_array("ROLE_CLIENT", $user->getRoles()) ) return $project->getOwner() === $user;
        if( in_array("ROLE_FREELANCER_PREMIUM", $user->getRoles()) ) return $this->projectRepository->isOnProject($user, $project->getOwner());
        return false;
    }
}

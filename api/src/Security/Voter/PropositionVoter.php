<?php

namespace App\Security\Voter;

use App\Entity\Proposition;
use App\Repository\PropositionRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PropositionVoter extends Voter
{
    public const MODIFY_PROPOSITION = 'MODIFY_PROPOSITION';

    public function __construct(private PropositionRepository $propositionRepository){}

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::MODIFY_PROPOSITION])
            && $subject instanceof Proposition;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        switch ($attribute) {
            case self::MODIFY_PROPOSITION:
                return in_array("ROLE_ADMIN", $user->getRoles()) || (in_array("ROLE_CLIENT", $user->getRoles()) && $subject->getStatus() === "AWAITING" && $subject->getProject()->getStatus() === "ACTIVE" && $subject->getProject()->getOwner() === $user);
                break;
        }
        return false;
    }
}

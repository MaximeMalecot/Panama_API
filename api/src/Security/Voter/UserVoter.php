<?php

namespace App\Security\Voter;

use DateInterval;
use App\Entity\User;
use DateTimeImmutable;
use App\Repository\ReviewRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    public const GET_CLIENT = 'GET_CLIENT';
    public const GET_FREELANCER = 'GET_FREELANCER';
    public const REVIEW_FREELANCER = 'REVIEW_FREELANCER';

    public function __construct(private ProjectRepository $projectRepository, private ReviewRepository $reviewRepository){}

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::GET_CLIENT, self::GET_FREELANCER, self::REVIEW_FREELANCER])
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
                return $this->canGetInfo($user, $subject);
                break;
            case self::GET_FREELANCER:
                return $this->canGetInfo($subject, $user);
                break;
            case self::REVIEW_FREELANCER:
                return $this->canReviewFreelancer($user, $subject);
                break;
        }

        return false;
    }

    private function canGetInfo(User $freelancer, User $client){
        if( in_array("ROLE_ADMIN", $freelancer->getRoles()) ) return true;
        //if( $client->getId() === $freelancer->getId() )      return true;
        
        if( in_array('ROLE_CLIENT', $client->getRoles()) 
            && in_array('ROLE_FREELANCER_PREMIUM', $freelancer->getRoles()) 
        ){
            return $this->projectRepository->hasCommonProject($client, $freelancer );
        }
        return false;
    }

    private function canReviewFreelancer(User $user, User $subject){
        if( in_array("ROLE_ADMIN", $user->getRoles()) ) return true;
        if( !in_array('ROLE_CLIENT', $user->getRoles()) || !in_array('ROLE_FREELANCER_PREMIUM', $subject->getRoles()) ){
            return false;
        }
        dump("first pass",$this->projectRepository->hasCommonPastProject($user, $subject),$this->reviewRepository->findBy([
            'client' => $user,
            'freelancer' => $subject
        ]));
        if(!$this->projectRepository->hasCommonPastProject($user, $subject)){
            return false;
        }
        $review = $this->reviewRepository->findBy([
            'client' => $user,
            'freelancer' => $subject
        ]);
        return empty($review);
    }

}

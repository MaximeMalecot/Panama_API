<?php

namespace App\Dto;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;

final class UserResetPasswordDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 255)]
    public $password;

    #[Assert\NotBlank]
    public $token;

    public function __construct(private RequestStack $requestStack, private EntityManagerInterface $em){}

    public function __invoke(UserRepository $userRepository)
    {
        // TODO: Implement __invoke() method.
        $token = json_decode($this->requestStack->getCurrentRequest()->getContent())->token;
        if (empty($token)){
            return json("Not found Token" , 404);
        }
        if(!$user = $userRepository->findOneBy(['reset_pwd_token' => $token]))
        {
            return $this->json("User not found", 404);
        }
        $user->setPassword(json_decode($this->requestStack->getCurrentRequest()->getContent())->password);
        $this->em->persist($user);
        $this->em->flush();
    }
}
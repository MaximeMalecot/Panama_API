<?php

namespace App\State;

use App\Entity\User;
use App\Dto\UserVerifyEmailDto;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class UserVerifyEmailProcessor implements ProcessorInterface
{

    public function __construct(private UserVerifyEmailDto $dto, private EntityManagerInterface $em, private UserPasswordHasherInterface $encoder){}

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): ?User
    {
        if (!$data instanceof UserVerifyEmailDto) {
            return null;
        }
        $user = $this->em->getRepository(User::class)->findOneBy(['verifyEmailToken' => $data->token]);
        if(!$user){
            throw new NotFoundHttpException('User not found');
        }
        $user->setIsVerified(true);
        $user->setVerifyEmailToken(null);
        $this->em->flush();
        return $user;
    }
}
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
    // #[Assert\Length(min: 8, max: 255)]
    public $password;

    #[Assert\NotBlank]
    public $token;

}
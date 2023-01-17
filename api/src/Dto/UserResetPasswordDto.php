<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class UserResetPasswordDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 255)]
    public $password;

    #[Assert\NotBlank]
    public $token;
}
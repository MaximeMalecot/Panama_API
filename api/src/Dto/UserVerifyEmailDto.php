<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class UserVerifyEmailDto
{
    #[Assert\NotBlank]
    public $token;
}
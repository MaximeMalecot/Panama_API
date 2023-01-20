<?php

namespace App\Dto;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;

final class FreelancerInfoKYCDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 9, max: 9)]
    public $ciret;

}
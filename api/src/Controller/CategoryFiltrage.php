<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CategoryFiltrage extends AbstractController
{
    public function __construct(private RequestStack $requestStack){}

    public function __invoke()
    {
        $filtre = $this->requestStack->getCurrentRequest()->getContent()->filtre;
        return $filtre;
    }
}
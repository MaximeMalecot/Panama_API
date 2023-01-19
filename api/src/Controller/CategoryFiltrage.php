<?php

namespace App\Controller;

use App\Repository\FilterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Serializer;

class CategoryFiltrage extends AbstractController
{
    public function __construct(private RequestStack $requestStack){}

    public function __invoke(FilterRepository $filterRepository)
    {
        $filters = $filterRepository->findAll();
        return $filters;
    }
}
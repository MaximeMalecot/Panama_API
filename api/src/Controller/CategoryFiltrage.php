<?php

namespace App\Controller;

use App\Entity\Filter;
use App\Repository\FilterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class CategoryFiltrage extends AbstractController
{
    public function __construct(private RequestStack $requestStack){}

    public function __invoke(FilterRepository $filterRepository)
    {
        $filters = $filterRepository->findAll();
        return $filters;
    }

    #[Route('/projects/collection', name: 'projects_collection', methods : ['POST'])]
    public function GetCollectionFilter(Request $request,FilterRepository $filterRepository, EntityManagerInterface $entityManager,SerializerInterface $serializer)
    {
        $content = json_decode($request->getContent());
        $value = [];
        if(!isset($content->name))
        {
            throw new NotFoundHttpException("Not found");
        }
        foreach ($filterRepository->findAllByCriteria($content->name) as $data)
        {
            array_push($value,$serializer->serialize($data,'json'));
        }
        return new JsonResponse($value);

    }
}
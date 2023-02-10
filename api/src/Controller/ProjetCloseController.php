<?php
namespace App\Controller;

use App\Entity\Project;
use App\Entity\Proposition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class ProjetCloseController extends AbstractController
{
    private RequestStack $requestStack;
    private EntityManagerInterface $em;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $em){
        $this->requestStack = $requestStack;
        $this->em = $em;
    }
    public function __invoke(Project $project)
    {
        $project->setStatus(Project::STATUS['ENDED']);
        $this->em->flush();
        return $project;
    }
}
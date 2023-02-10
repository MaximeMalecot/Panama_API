<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class FreelancerGetProjectsController extends AbstractController
{
    private RequestStack $requestStack;
    private EntityManagerInterface $em;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $em){
        $this->requestStack = $requestStack;
        $this->em = $em;
    }
    public function __invoke(User $data)
    {
        $status = $this->requestStack->getCurrentRequest()->get('status');
        $projects = $this->em->getRepository(Project::class)->getFreelancerProjects($data, $status);
        return $projects;
    }
}
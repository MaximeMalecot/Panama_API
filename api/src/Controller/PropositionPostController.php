<?php
namespace App\Controller;

use App\Entity\Project;
use App\Entity\Proposition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class PropositionPostController extends AbstractController
{
    private RequestStack $requestStack;
    private EntityManagerInterface $em;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $em){
        $this->requestStack = $requestStack;
        $this->em = $em;
    }
    public function __invoke(Project $project)
    {
        $proposition = (new Proposition())
            ->setProject($project)
            ->setFreelancer($this->getUser());
        $this->em->persist($proposition);
        $this->em->flush();
        return $this->json([
            'proposition' => $proposition
        ], 201);
    }
}
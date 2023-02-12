<?php
namespace App\Controller;

use App\Entity\Project;
use App\Entity\Proposition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class PropositionGetOwnController extends AbstractController
{

    public function __construct(private RequestStack $requestStack, private EntityManagerInterface $em){}
    
    public function __invoke(Project $data)
    {
        $user = $this->getUser();
        $proposition = $this->em->getRepository(Proposition::class)->findOneBy(['project' => $data, 'freelancer' => $user]);
        return $proposition;
    }
}
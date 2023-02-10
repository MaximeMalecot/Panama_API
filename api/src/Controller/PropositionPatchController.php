<?php
namespace App\Controller;

use App\Entity\Project;
use App\Entity\Proposition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class PropositionPatchController extends AbstractController
{
    private RequestStack $requestStack;
    private EntityManagerInterface $em;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $em){
        $this->requestStack = $requestStack;
        $this->em = $em;
    }
    public function __invoke(Proposition $proposition)
    {
        $status = json_decode($this->requestStack->getCurrentRequest()->getContent())->status;
        if(!$status || ($status !== "ACCEPTED" && $status !== "REFUSED")){
            return $this->json("Status not found", 404);
        }
        $proposition->setStatus($status);
        if($status === "ACCEPTED"){
            $proposition->getProject()->setStatus("IN_PROGRESS");
            foreach($proposition->getProject()->getPropositions() as $p){
                if($p->getId() !== $proposition->getId()){
                    $p->setStatus("REFUSED");
                }
            }
        }
        $this->em->flush();
        return $proposition;
    }
}
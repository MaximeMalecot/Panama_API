<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Review;
use App\Entity\Project;
use App\Entity\Proposition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class ReviewPostController extends AbstractController
{

    public function __construct(private RequestStack $requestStack, private EntityManagerInterface $em){}
    
    public function __invoke(User $data)
    {
        $jsonData = json_decode($this->requestStack->getCurrentRequest()->getContent());
        $review = (new Review())
            ->setClient($this->getUser())
            ->setFreelancer($data)
            ->setMark($jsonData->mark)
            ->setContent($jsonData->content);
        $this->em->persist($review);
        $this->em->flush();
        return $review;
    }
}
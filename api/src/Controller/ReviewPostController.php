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
    
    public function __invoke()
    {
        $client = $this->getUser();

        $data = json_decode($this->requestStack->getCurrentRequest()->getContent());
        $freelancer = $this->em->getRepository(User::class)->find($data->freelancerId);
        if(!$freelancer){
            return $this->json("Freelancer not found", 404);
        }
        $mark = $data->mark;
        $content = $data->content;

        if($this->em->getRepository(Project::class)->hasCommonProject($client, $freelancer)){
            if($this->em->getRepository(Review::class)->findBy([
                'client' => $client,
                'freelancer' => $freelancer
            ])){
                return $this->json("You already reviewed this freelancer", 403);
            }
            $review = (new Review())
                ->setClient($client)
                ->setFreelancer($freelancer)
                ->setMark($mark)
                ->setContent($content);
            $this->em->persist($review);
            $this->em->flush();
            return $review;
        } else {
            return $this->json("You can't review this freelancer", 403);
        }
    }
}
<?php
namespace App\Controller;

use Stripe\StripeClient;
use App\Entity\SubscriptionPlan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class SubscriptionPlanPostController extends AbstractController
{
    private StripeClient $stripeClient;
    private RequestStack $requestStack;
    private EntityManagerInterface $em;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $em){
        $this->stripeClient = new StripeClient($_ENV['STRIPE_SK']);
        $this->requestStack = $requestStack;
        $this->em = $em;
    }
    public function __invoke()
    {
        $data = json_decode($this->requestStack->getCurrentRequest()->getContent());
        if(!isset($data->name) || !isset($data->description) || !isset($data->color) || !isset($data->stripeId)){
            return $this->json("Missing data", 400);
        }
        $subscription = $this->em->getRepository(SubscriptionPlan::class)->findOneBy(['stripeId' => $data->stripeId]);
        if($subscription){
            return $this->json("Stripe price already used", 400);
        }
        try{
            $price = $this->stripeClient->prices->retrieve($data->stripeId);
        } catch(\Exception $e){
            return $this->json("Stripe price not found", 404);
        }
        $subscriptionPlan = (new SubscriptionPlan())
            ->setName($data->name)
            ->setDescription($data->description)
            ->setColor($data->color)
            ->setPrice($price->unit_amount/100)
            ->setStripeId($data->stripeId);
        $this->em->persist($subscriptionPlan);
        $this->em->flush();
        return $this->json($subscriptionPlan, 201);
    }
}
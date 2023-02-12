<?php
namespace App\Controller;

use Stripe\StripeClient;
use App\Entity\SubscriptionPlan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\SubscriptionPlanService;

#[AsController]
class SubscriptionPlanPostController extends AbstractController
{
    private StripeClient $stripeClient;
    private RequestStack $requestStack;
    private EntityManagerInterface $em;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $em, SubscriptionPlanService $subscriptionPlanService){
        $this->stripeClient = new StripeClient($_ENV['STRIPE_SK']);
        $this->requestStack = $requestStack;
        $this->em = $em;
        $this->subscriptionPlanService = $subscriptionPlanService;
    }
    public function __invoke()
    {
        $data = json_decode($this->requestStack->getCurrentRequest()->getContent());
        if(!isset($data->name) || !isset($data->description) || !isset($data->color) || !isset($data->price)){
            return $this->json("Missing data", 400);
        }

        if( !is_numeric($data->price) && floatval($data->price) < 1){
            return $this->json("Price must be greater than 0", 400);
        }
        
        try{
            $plan = $this->subscriptionPlanService->createSubscriptionPlan($data->name, $data->price);
            if(!$plan){
                throw new \Exception('An error occurred, could not create this plan');
            }
            $plan->setColor($data->color);
            $plan->setDescription($data->description);
        } catch(\Exception $e){
            return $this->json($e->getMessage(), 404);
        }
        $this->em->flush();
        return $plan;
    }
}
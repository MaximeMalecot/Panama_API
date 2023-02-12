<?php
namespace App\Service;

use App\Entity\SubscriptionPlan;
use App\Repository\SubscriptionPlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Stripe\StripeClient;

class SubscriptionPlanService
{
    public function __construct(
                                private EntityManagerInterface $manager
    )
    {
        $this->stripeClient = new StripeClient($_ENV['STRIPE_SK']);
    }

    public function createSubscriptionPlan($name, $price)
    {

        if ( empty($name) || empty($price)) {
            throw new \Exception('An error occurred, missing parameters');
        }

        $product = $this->createProduct();
        if(!$product){
            throw new \Exception('An error occurred, could not create this product');
        }

        $plan = $this->stripeClient->prices->create([
            'unit_amount' => floatval($price)*100,
            'currency' => 'eur',
            'recurring' => ['interval' => 'month'],
            'product' => $product,
        ]);

        if(!$plan || empty($plan->id)){
            throw new \Exception('An error occurred, could not create this plan');
        }

        $subscriptionPlan = $this->savePlan($plan->id, $price, $name);
        if(!$subscriptionPlan->getId()){
            throw new \Exception('An error occurred, could not save this plan');
        }
        return $subscriptionPlan;
    }

    public function createProduct()
    {
        $product = $this->stripeClient->products->create([
            'name' => 'Subscription',
        ]);

        return $product->id;
    }

    private function savePlan(string $stripeId, int $price, string $name): ?SubscriptionPlan
    {
        $plan = new SubscriptionPlan();
        $plan->setName($name);
        $plan->setDescription("Subscription plan");
        $plan->setPrice($price);
        $plan->setStripeId($stripeId);
        $plan->setColor("#000000");
        $this->manager->persist($plan);
        $this->manager->flush();

        return $plan;
    }
}
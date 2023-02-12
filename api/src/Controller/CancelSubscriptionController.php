<?php
namespace App\Controller;

use Stripe\StripeClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class CancelSubscriptionController extends AbstractController
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
        $user = $this->getUser();
        $stripe_customer = $this->stripeClient->customers->retrieve($user->getStripeId(), [ 'expand' => ['subscriptions'] ]);
        $subscription = $stripe_customer->subscriptions->data[0];
        $this->stripeClient->subscriptions->cancel($subscription->id);
        return $this->json(["message" => "subscription canceled"], 204);
    }
}
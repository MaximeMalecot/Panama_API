<?php

namespace App\Controller;

use Stripe\Webhook;
use Stripe\StripeClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/payment')]
#[AsController]
class PaymentController extends AbstractController
{   

    private $stripeClient;

    public function __construct(){
        $this->stripeClient = new StripeClient($_ENV['STRIPE_SK']);
    }

    #[Route('/create_payment', name: 'payment_create', methods : ['POST'])]
    public function createPayment(Request $request)
    {
        $body = $request->getContent();
        $url = $_ENV['FRONT_URL'];
        $expires_at = ((new \DateTime())->modify('+1 hour'))->getTimeStamp();

        $project = (new Project())
            ->setName()
            ->setDescription()
            ->setOwner($this->getUser());

        $price = $this->stripeClient->prices->create([
            'unit_amount' => $body->price * 100,
            'currency' => 'eur',
            'product' => $body->title,
        ]);

        $session = $this->stripeClient->checkout->sessions->create([
            'success_url' => "$url?success",
            'cancel_url' => "$url?failed",
            'line_items' => [
                [
                    'price' => $price->id,
                    'quantity' => 1,
                ],
            ],
            'expires_at' => $expires_at,
            'mode' => 'payment',
        ]);

        $invoice = (new Invoice())
            ->setAmount($price->unit_amount)
            ->setStatus('CREATED')
            ->setClient($this->getUser())
            ->setProject($project)
            ->setIdStripe($session->payment_intent);
        return $this->json($session, 200);
        //create payment intent 
    }

    #[Route('/create_subscription', name: 'subscription_create', methods : ['POST'])]
    public function createSubscription(Request $request)
    {
        $body = $request->getContent();
        //create subscription
    }
}

?>
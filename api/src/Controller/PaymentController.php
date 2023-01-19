<?php

namespace App\Controller;

use Stripe\Webhook;
use App\Entity\Invoice;
use App\Entity\Project;
use Stripe\StripeClient;
use App\Repository\FilterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/payment')]
class PaymentController extends AbstractController
{   

    private $stripeClient;

    public function __construct(){
        $this->stripeClient = new StripeClient($_ENV['STRIPE_SK']);
    }

    #[Route('/create_checkout', name: 'payment_create', methods : ['POST'])]
    public function createPayment(Request $request, FilterRepository $filterRepository, EntityManagerInterface $em)
    {
        $body = json_decode($request->getContent(), true);
        $project = (new Project())
                    ->setName($body['name'])
                    ->setDescription($body['description'])
                    ->setOwner($this->getUser())
                    ->setMinPrice($body['minPrice'])
                    ->setMaxPrice($body['maxPrice']);
        foreach($body['filters'] as $filter){
            $entityFilter = $filterRepository->find($filter);
            if($entityFilter){
                $project->addFilter($entityFilter);
            }
        }
        $em->persist($project);
        $em->flush();

        $url = $_ENV['FRONT_URL'];
        $expires_at = ((new \DateTime())->modify('+1 hour'))->getTimeStamp();

        // $price = $this->stripeClient->prices->create([
        //     'unit_amount' => 40 * 100,
        //     'currency' => 'eur'
        // ]);

        $session = $this->stripeClient->checkout->sessions->create([
            'success_url' => "$url?success",
            'cancel_url' => "$url?failed",
            'expires_at' => $expires_at,
            'line_items' => [[
                'price_data' => [
                  'currency' => 'eur',
                  'unit_amount' => 40*100,
                  'product_data' => [
                    'name' => 'Project creation',
                    'description' => 'Post your own project on panama and find the perfect freelancer for your project',
                  ],
                ],
                'quantity' => 1,
              ]],
            'mode' => 'payment',
        ]);

        $invoice = (new Invoice())
            ->setAmount(40)
            ->setStatus('CREATED')
            ->setClient($this->getUser())
            ->setProject($project)
            ->setIdStripe($session->payment_intent);
        $em->persist($invoice);
        $em->flush();
        if(!$session->url){
            return $this->json(['error' => 'error'], 500);
        }
        return $this->json([
            'url' => $session->url
        ], 200);
    }

    #[Route('/create_subscription', name: 'subscription_create', methods : ['POST'])]
    public function createSubscription(Request $request)
    {
        $body = $request->getContent();
        //create subscription
    }
}

?>
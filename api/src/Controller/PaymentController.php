<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Project;
use Stripe\StripeClient;
use App\Entity\Subscription;
use App\Repository\FilterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\SubscriptionPlanRepository;
use Symfony\Component\Routing\Annotation\Route;
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

        $user = $this->getUser();
        if($user->getIsVerified() === false){
            return $this->json(['error' => 'You must verify your account before creating a project'], 400);
        }
        $clientInfos = $user->getClientInfo();	
        if(!$clientInfos->getPhoneNb() || !$clientInfos->getAddress() || !$clientInfos->getCity()){
            return $this->json(['error' => 'You must specify your phone number, address and city before creating a project'], 400);
        }

        if(!$user->getStripeId()){
            $customer = $this->stripeClient->customers->create([
                'email' => $user->getEmail(),
                'name' => $user->getName().' '.$user->getSurname(),
            ]);
            $user->setStripeId($customer->id);
        }
        
        if(!isset($body['name']) || !isset($body['description']) || !isset($body['minPrice']) || !isset($body['maxPrice']) || !isset($body['length'])){
            return $this->json(['error' => 'Missing parameters'], 400);
        }

        $project = (new Project())
                    ->setName($body['name'])
                    ->setDescription($body['description'])
                    ->setOwner($this->getUser())
                    ->setMinPrice($body['minPrice'])
                    ->setMaxPrice($body['maxPrice'])
                    ->setLength($body['length'])
                    ;

        if( isset($body['filters']) && is_array($body['filters']) && count($body['filters']) > 0 ){
            foreach($body['filters'] as $filter){
                $entityFilter = $filterRepository->find($filter);
                if($entityFilter){
                    $project->addFilter($entityFilter);
                }
            }
        }

        $em->persist($project);
        $em->flush();

        $url = $_ENV['FRONT_URL'].'/redirection/payment';
        $expires_at = ((new \DateTime())->modify('+1 hour'))->getTimeStamp();

        $session = $this->stripeClient->checkout->sessions->create([
            'success_url' => "$url?success=true",
            'cancel_url' => "$url?success=false",
            'expires_at' => $expires_at,
            'customer' => $user->getStripeId(),
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
    public function createSubscription(Request $request, SubscriptionPlanRepository $subscriptionPlanRepository, EntityManagerInterface $em)
    {
        $body = json_decode($request->getContent(), true);
        $subscriptionPlan = $subscriptionPlanRepository->find($body['subscriptionPlan']);
        if(!$subscriptionPlan){
            return $this->json(['message' => 'Unknow subscription plan'], 500);
        }
        $user = $this->getUser();
        if($user->getIsVerified() === false){
            return $this->json(['error' => 'You must verify your account before having a subscription'], 400);
        }
        if($user->getFreelancerInfo()->getIsVerified() === false){
            return $this->json(['error' => 'You must verify your freelancer status before having a subscription'], 400);
        }

        if(!$user->getStripeId()){
            $customer = $this->stripeClient->customers->create([
                'email' => $user->getEmail(),
                'name' => $user->getName().' '.$user->getSurname(),
            ]);
            $user->setStripeId($customer->id);
        }

        if($user->getSubscription()){
            if($user->getSubscription()->getIsActive() === true){
                return $this->json(['message' => 'You already have a subscription'], 500);
            } else{
                $subscription = $user->getSubscription();
                $subscription->setPlan($subscriptionPlan);
            }
        }else{
            $subscription = (new Subscription())
                ->setPlan($subscriptionPlan)
                ->setFreelancer($user)
            ;
        }

        $url = $_ENV['FRONT_URL'].'/redirection/subscription';
        $expires_at = ((new \DateTime())->modify('+1 hour'))->getTimeStamp();

        $session = $this->stripeClient->checkout->sessions->create([
            'success_url' => "$url?success=true",
            'customer' => $user->getStripeId(),
            'cancel_url' => "$url?success=failed",
            'expires_at' => $expires_at,
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $subscriptionPlan->getStripeId(),
                'quantity' => 1,
            ]],
        ]);

        $em->persist($subscription);
        $em->flush();;

        if(!$session->url){
            return $this->json(['error' => 'error'], 500);
        }
        return $this->json([
            'url' => $session->url
        ], 200);
    }
}

?>
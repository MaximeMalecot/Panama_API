<?php

namespace App\Controller;

use Stripe\Webhook;
use App\Entity\Invoice;
use App\Entity\Project;
use Stripe\StripeClient;
use App\Repository\UserRepository;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/webhook')]
#[AsController]
class WebhookController extends AbstractController
{   

    private $stripeClient;

    public function __construct(){
        $this->stripeClient = new StripeClient($_ENV['STRIPE_SK']);
    }

    #[Route('/stripe', name: 'stripe_payment')]
    public function stripePayment(Request $request, EntityManagerInterface $em, InvoiceRepository $invoiceRepository, UserRepository $userRepository)
    {
        $webhookSecret = $_ENV['STRIPE_WH_PAYMENT_SK'];
        $signature = $request->headers->get('stripe-signature');

        try{
            if( !$webhookSecret) { throw new \Exception('Missing secret'); }

            $event = Webhook::constructEvent(
                $request->getcontent(), $signature, $webhookSecret
            );
            
            $object = $event['data']['object'];
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $invoice = $invoiceRepository->findOneBy(['idStripe' => $object['id']]);
                    if(!$invoice)  { throw new \Exception('No invoice'); }
                    if($invoice->getProject()){
                        $invoice->setStatus(Invoice::STATUS['PAID']);
                        $invoice->getProject()->setStatus(Project::STATUS['ACTIVE']);
                        $em->flush();
                    }
                    return $this->json($event->data->object, 204);
                case 'invoice.paid':
                    $user = $userRepository->findOneBy(['stripeId' => $object['customer']]);
                    $subscription = $user->getSubscription();
                    if(!$subscription) { throw new \Exception('No subscription'); }
                    if($subscription->getIsActive()) { 
                        return $this->json('Already active', 200);
                    }
                    $subscription->setIsActive(true);
                    $em->flush();
                    return $this->json('Subscription activated', 204);
                case 'invoice.payment_failed':
                case 'customer.subscription.deleted':
                    $user = $userRepository->findOneBy(['stripeId' => $object['customer']]);
                    $subscription = $user->getSubscription();
                    if(!$subscription) { throw new \Exception('No subscription'); }
                    if(!$subscription->getIsActive()) { 
                        return $this->json('Already canceled', 200);
                    }
                    $subscription->setIsActive(false);
                    $em->flush();
                    return $this->json('Subscription canceled', 204);
                default:
                    return $this->json('Unhandled eventType', 400);
            }
        }catch(\Exception $e){
            return $this->json($e->getMessage(), 500);
        }
    }
}
?>
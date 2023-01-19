<?php

namespace App\Controller;

use Stripe\Webhook;
use Stripe\StripeClient;
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

    #[Route('/stripe_payment', name: 'stripe_payment')]
    public function stripePayment(Request $request)
    {
        $webhookSecret = $_ENV['STRIPE_WH_PAYMENT_SK'];
        $signature = $request->headers->get('stripe-signature');

        if( !$webhookSecret) { throw new \Exception('Missing secret'); }

        $event = Webhook::constructEvent(
            $request->getcontent(), $signature, $webhookSecret
        );
        
        switch ($event->type) {
            case 'payment_intent.succeeded':
              return $this->json($event->data->object, 200);
            default:
              return $this->json($event->data->object, 400);
        }
    }

    #[Route('/stripe_subscription', name: 'stripe_subscription')]
    public function stripeSubscription(Request $request)
    {
        $webhookSecret = $_ENV['STRIPE_WH_SUBSCRIPTION_SK'];
        $signature = $request->headers->get('stripe-signature');

        if( !$webhookSecret) { throw new \Exception('Missing secret'); }

        $event = Webhook::constructEvent(
            $request->getcontent(), $signature, $webhookSecret
          );
        
        switch ($event->type) {
            case 'invoice.paid':
                // Payement effectué et passage de l'abonnement à active
                dump('succeded');
                return $this->json($event->data->object, 200);
            case 'customer.subscription.deleted':
                //La souscription vient d'etre supprimée
                break;
            case 'invoice.payment_failed':
            case 'invoice.payment_action_required':
            case 'invoice.finalization_failed':
                //delete l'object Invoice et le Project associé
                // $invoice = $invoiceRepository->findOneBy(['stripePI' => $object['id']]);
                // if(!$invoice) throw new \Exception('No invoice linked');
                // $paymentService->removeInvoice($invoice);
                return $this->json($event->data->object, 400);
            default:
              return $this->json($event->data->object, 400);
        }
    }


}
?>
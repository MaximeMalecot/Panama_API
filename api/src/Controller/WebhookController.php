<?php

namespace App\Controller;

use Stripe\Webhook;
use App\Entity\User;
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
        $webhookSecret = $_ENV['STRIPE_WH_SK'];
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
                    $user->setRoles(['ROLE_FREELANCER_PREMIUM']);
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
                    $user->setRoles(['ROLE_FREELANCER']);
                    $em->flush();
                    return $this->json('Subscription canceled', 204);
                default:
                    return $this->json('Unhandled eventType', 400);
            }
        }catch(\Exception $e){
            return $this->json($e->getMessage(), 500);
        }
    }

    #[Route('/kyc_verification/{id}', name: 'kyc_verification', methods: ['POST'])]
    public function kycVerification(Request $request, EntityManagerInterface $em, UserRepository $userRepository){
        $validStatus = ['success', 'failed'];
        $userId = $request->get("id");
        $status = $request->query->get("status");
        $user = $userRepository->findOneBy(['id' => $userId]);
        $receivedApiSecret = $request->headers->get('authorization');

        try{
            if(!$receivedApiSecret)                                                         throw new \Exception("Missing secret");
            if($receivedApiSecret !== $_ENV['KYC_API_SECRET'])                              throw new \Exception("Invalid secret");
            if(!$user)                                                                      throw new \Exception("User not found");
            if (!in_array("ROLE_FREELANCER", $user->getRoles()))                            throw new \Exception("Invalid role");
            if ($user->getFreelancerInfo() && $user->getFreelancerInfo()->getIsVerified() ) throw new \Exception("Already verified");
            if(!in_array($status, $validStatus))                                            throw new \Exception("Invalid status");
        }catch(\Exception $e){
            return $this->json($e->getMessage(), 400);
        }

        if($status === 'failed') return $this->json(200);
        $user->getFreelancerInfo()->setIsVerified(true);
        $em->flush();
        return $this->json(200);
    }

}
?>
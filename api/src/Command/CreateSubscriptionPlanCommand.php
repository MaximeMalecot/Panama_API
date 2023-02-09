<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Stripe\StripeClient;
use App\Entity\SubscriptionPlan;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Entity\User;

#[AsCommand(
    name: 'createSubPlan',
    description: 'Create a new subscription plan',
)]
class CreateSubscriptionPlanCommand extends Command
{

    public function __construct(
                                private EntityManagerInterface $manager
    )
    {
        parent::__construct();
        $this->stripeClient = new StripeClient($_ENV['STRIPE_SK']);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'Plan name (optional, default: PLAN)')
            ->addArgument('price', InputArgument::OPTIONAL, 'Price (optional, default: 9,99$)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $price = $input->getArgument('price');

        try{
        
            if ( empty($name)) {
                $name = "PLAN";
            }
    
            if ( empty($price)) {
                $price = 9.99*100;
            }

            $product = $this->createProduct();
            if(!$product){
                throw new \Exception('An error occurred, could not create this product');
            }

            $striplePlan = $this->stripeClient->plans->create([
                'amount' => intval($price),
                'currency' => 'eur',
                'interval' => 'month',
                'product' => $product,
            ]);
    
            if(!$striplePlan){
                throw new \Exception('An error occurred, could not create this user');
            }
            
            $plan = $this->savePlan($striplePlan->id, $price, $name);
            if(!$plan){
                throw new \Exception('An error occurred, could not save this plan');
            }

            $io->success("Plan created!");
            return Command::SUCCESS;

        }catch(\Exception $e){
            $io->error($e->getMessage());
            return Command::FAILURE;       
        }

    }

    private function createProduct(): ?string
    {
        $product = $this->stripeClient->products->create([
            'name' => 'Subscription',
        ]);

        if(!$product){
            return false;
        }

        return $product->id;
    }

    private function savePlan(string $planId, int $price, string $name): bool
    {
        $plan = new SubscriptionPlan();
        $plan->setName($name);
        $plan->setDescription("Subscription plan");
        $plan->setPrice($price);
        $plan->setStripeId($planId);
        $plan->setColor("#000000");
        $this->manager->persist($plan);
        $this->manager->flush();

        return true;
    }

}

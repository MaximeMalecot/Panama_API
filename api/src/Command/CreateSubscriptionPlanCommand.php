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
use App\Service\SubscriptionPlanService;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Entity\User;

#[AsCommand(
    name: 'createSubPlan',
    description: 'Create a new subscription plan',
)]
class CreateSubscriptionPlanCommand extends Command
{

    public function __construct(
                                private SubscriptionPlanService $subscriptionPlanService
    )
    {
        parent::__construct();
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

            $plan = $this->subscriptionPlanService->createSubscriptionPlan($name, $price);
            if(!$plan){
                throw new \Exception('An error occurred, could not create this plan');
            }
            $io->success("Plan created!");
            return Command::SUCCESS;

        }catch(\Exception $e){
            $io->error($e->getMessage());
            return Command::FAILURE;       
        }

    }

}

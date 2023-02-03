<?php
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class CancelSubscriptionController extends AbstractController
{

    public function __construct(private RequestStack $requestStack, private EntityManagerInterface $em){}

    public function __invoke()
    {
        dump($this->requestStack->getCurrentRequest());
    }
}
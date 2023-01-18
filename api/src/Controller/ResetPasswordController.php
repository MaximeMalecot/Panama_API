<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class ResetPasswordController extends AbstractController
{

    public function __construct(private RequestStack $requestStack, private EntityManagerInterface $em){}

    public function __invoke()
    {
        $email = json_decode($this->requestStack->getCurrentRequest()->getContent())->email;

        if(!$user = $this->em->getRepository(User::class)->findOneBy(['email' => $email])){
            throw $this->createNotFoundException('User not found');
        }

        $user->setResetPwdToken(bin2hex(random_bytes(32)));
        $this->em->flush();

        // SEND EMAIL WITH LINK AND TOKEN


        return $this->json("Email send", 204);
    }
}
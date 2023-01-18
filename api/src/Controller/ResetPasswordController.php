<?php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Context;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[AsController]
class ResetPasswordController extends AbstractController
{

    public function __construct(private RequestStack $requestStack, private EntityManagerInterface $em){}

    public function __invoke(MailerInterface $mailer,UserRepository $userRepository)
    {
        $email = json_decode($this->requestStack->getCurrentRequest()->getContent())->email;

        if(!$user = $userRepository->findOneBy(['email' => $email])){
            return $this->json("Email not found", 404);
        }
        $user->setResetPwdToken(bin2hex(random_bytes(32)));
        $user->setResetPwdTokenTime(new \DateTime());
        $this->em->flush();

        // SEND EMAIL WITH LINK AND TOKEN
        $emailconfig = (new TemplatedEmail())
            ->from(new Address('panama@easylocmoto.fr','Panama Agency'))
            ->to($email)
            ->subject('Reset Your Password')
            ->htmlTemplate('mail/Reset-password.html.twig')
            ->context(['name'=> $user->getSurname(),
                        'token' => $user->getResetPwdToken()]);
        $mailer->send($emailconfig);

        return $this->json("Email send", 202);
    }
}
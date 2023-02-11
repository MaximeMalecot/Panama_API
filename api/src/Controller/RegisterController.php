<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\ClientInfo;
use App\Entity\FreelancerInfo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[AsController]
class RegisterController extends AbstractController
{

    public function __construct(private RequestStack $requestStack, private EntityManagerInterface $em, private UserPasswordHasherInterface $encoder){}

    public function __invoke(MailerInterface $mailer)
    {
        $name = json_decode($this->requestStack->getCurrentRequest()->getContent())->name;
        $surname = json_decode($this->requestStack->getCurrentRequest()->getContent())->surname;
        $email = json_decode($this->requestStack->getCurrentRequest()->getContent())->email;
        $plainPassword = json_decode($this->requestStack->getCurrentRequest()->getContent())->plainPassword;
        $roles = json_decode($this->requestStack->getCurrentRequest()->getContent())->roles;
        $gravatar = "https://www.gravatar.com/avatar/".md5(strtolower(trim($email)));

        if($user = $this->em->getRepository(User::class)->findOneBy(['email' => $email])){
            throw $this->createNotFoundException('User with email already exists');
        }

        $user = (new User())
                 ->setName($name)
                 ->setSurname($surname)
                 ->setEmail($email)
                 ->setPlainPassword($plainPassword)
                 ->setRoles($roles)
                 ->setGravatarImage($gravatar)
                 ->setVerifyEmailToken(bin2hex(random_bytes(32)));
        $user->setPassword($this->encoder->hashPassword($user, $user->getPlainPassword()));

        if( !in_array("ROLE_CLIENT", $roles) && !in_array("ROLE_FREELANCER", $roles) || in_array("ROLE_ADMIN", $roles) || in_array("ROLE_FREELANCER_PREMIUM", $roles)){
            throw $this->createNotFoundException('Unknown role');
        }

        if( in_array("ROLE_CLIENT", $roles) ){
            $profile = (new ClientInfo())->setClient($user);
            $this->em->persist($profile);
        }

        if( in_array("ROLE_FREELANCER", $roles) ){
            $profile = (new FreelancerInfo())->setFreelancer($user);
            $this->em->persist($profile);
        }

        $emailconfig = (new TemplatedEmail())
            ->from(new Address('panama@easylocmoto.fr','Panama Agency'))
            ->to($email)
            ->subject('Verify your account')
            ->htmlTemplate('mail/Verify-account.html.twig')
            ->context([
                'name'=> $user->getName(). " ".$user->getSurname(),
                'token' => $user->getVerifyEmailToken(),
                'url' => $_ENV['FRONT_URL']
            ]);
        $mailer->send($emailconfig);


        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }
}
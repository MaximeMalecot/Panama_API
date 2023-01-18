<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
class RegisterController extends AbstractController
{

    public function __construct(private RequestStack $requestStack, private EntityManagerInterface $em, private UserPasswordHasherInterface $encoder){}

    public function __invoke()
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


        $this->em->persist($user);
        $this->em->flush();

        // SEND EMAIL WITH LINK AND TOKEN

        return $this->json("Created", 201);
    }
}
<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class RegisterController extends AbstractController
{

    public function __construct(private RequestStack $requestStack, private EntityManagerInterface $em){}

    public function __invoke()
    {
        $name = json_decode($this->requestStack->getCurrentRequest()->getContent())->name;
        $surname = json_decode($this->requestStack->getCurrentRequest()->getContent())->surname;
        $email = json_decode($this->requestStack->getCurrentRequest()->getContent())->email;
        $plainPassword = json_decode($this->requestStack->getCurrentRequest()->getContent())->password;
        $confirmPassword = json_decode($this->requestStack->getCurrentRequest()->getContent())->confirmPassword;
        $role = json_decode($this->requestStack->getCurrentRequest()->getContent())->role;
        $gravatar = "https://www.gravatar.com/avatar/".md5(strtolower(trim($email)));
        $user->setResetPwdToken(bin2hex(random_bytes(32)));

        if($user = $this->em->getRepository(User::class)->findOneBy(['email' => $email])){
            throw $this->createNotFoundException('User with email already exists');
        }

        if($plainPassword !== $confirmPassword){
            throw $this->createNotFoundException('Passwords do not match');
        }

        $user = (new User())
                 ->setName($name)
                 ->setSurname($surname)
                 ->setEmail($email)
                 ->setPlainPassword($plainPassword)
                 ->setRoles([$role])
                 ->setGravatar($gravatar)
                 ->setVerifyEmailToken(bin2hex(random_bytes(32)));
        

        // SEND EMAIL WITH LINK AND TOKEN

        return $this->json("Success");
    }
}
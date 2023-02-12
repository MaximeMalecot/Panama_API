<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class UserMailer
{

    public function __construct(private MailerInterface $mailer){}

    public function sendRoleElevation(User $user, string $text = null)
    {
        $email = (new TemplatedEmail())
            ->from(new Address('panama@easylocmoto.fr','Panama Agency'))
            ->to($user->getEmail())
            ->subject('Modification de votre compte')
            ->htmlTemplate('mail/role-elevation.html.twig')
            ->context([
                'name'=> $user->getName(). " ".$user->getSurname(),
                'text' => $text,
                'url' => $_ENV['FRONT_URL']
            ]);
        $this->mailer->send($email);
    }
}
<?php

namespace App\Entity;

use App\Entity\Pizza;
use Gedmo\Mapping\Annotation as Gedmo;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use App\Dto\UserResetPasswordDto;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\UserResetPasswordProcessor;
use App\Controller\ResetPasswordController;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ApiResource]
#[Get(
    normalizationContext: [
        'groups' => ['user_get']
    ]
)]
#[GetCollection(
    normalizationContext: [
        'groups' => ['user_cget']
    ]
)]
#[Patch(
        name: 'forgot_password', 
        uriTemplate: '/users/forgot-password', 
        controller: ResetPasswordController::class,
        denormalizationContext: [
            'groups' => ['user_resetPwd_request']
        ],
        normalizationContext: [
            'groups' => ['user_resetPwd']
        ]
    )
]
#[Patch(
    name: 'resetPwd', 
    uriTemplate: '/users/updatePwd', 
    input: UserResetPasswordDto::class,
    output: User::class,
    processor: UserResetPasswordProcessor::class
)]
#[Put(
    security: "is_granted('ROLE_ADMIN') or object.getOwner() == user",
    denormalizationContext: [
        'groups' => ['user_modify']
    ],
    normalizationContext: [
        'groups' => ['user_get']
    ]
)]
#[Post(
    name: 'register', 
    uriTemplate: '/users/register',
    denormalizationContext: [
        'groups' => ['user_register']
    ],
    normalizationContext: [
        'groups' => ['user_cget']
    ]
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity('email', message: 'Email déjà utilisé')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[NotBlank()]
    #[NotNull()]
    #[Email()]
    #[Groups(["user_cget", "user_get", "user_register", "user_resetPwd", "user_resetPwd_request"])]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(["user_get"])]
    // #[Regex("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/i", message: "Must be minimum eight characters, at least one letter and one number ")]
    private ?string $password = null;

    #[Groups(["user_changePwd", "user_register"])]
    private ?string $plainPassword = null;

    private ?string $oldPassword = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["user_resetPwd", "user_changePwd"])]
    private ?string $resetPwdToken = null;

    public function __construct()
    {
        $this->pizzas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(?string $oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function getResetPwdToken(): ?string
    {
        return $this->resetPwdToken;
    }

    public function setResetPwdToken(?string $resetPwdToken): self
    {
        $this->resetPwdToken = $resetPwdToken;

        return $this;
    }
}

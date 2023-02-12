<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use App\Dto\UserVerifyEmailDto;
use Doctrine\ORM\Mapping as ORM;
use App\Dto\UserResetPasswordDto;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\ApiResource;
use App\Controller\RegisterController;
use ApiPlatform\Metadata\GetCollection;
use App\State\UserVerifyEmailProcessor;
use App\Controller\ReviewPostController;
use App\Entity\Traits\TimestampableTrait;
use App\State\UserResetPasswordProcessor;
use App\Controller\ResetPasswordController;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Email;
use App\Controller\FreelancerGetProjectsController;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ApiResource]
#[Get(
    security: 'is_granted("ROLE_ADMIN") or object == user',
    normalizationContext: [
        'groups' => ['user_get', 'timestamp']
    ]
)]
#[Get(
    security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_CLIENT') and object == user",
    uriTemplate: '/users/{id}/projects',
    normalizationContext: [
        'groups' => ['user_get_projects']
    ]
)]
#[Get(
    security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_FREELANCER') and object == user",
    uriTemplate: '/users/{id}/freelancer/projects',
    controller: FreelancerGetProjectsController::class,
    normalizationContext: [
        'groups' => ['project_freelancer_own']
    ]
)]
#[Get(
    security: 'is_granted("GET_CLIENT", object)',
    uriTemplate: '/users/clients/{id}',
    normalizationContext: [
        'groups' => ['user_get', 'specific_client_get']
    ]
)]
#[Get(
    security: 'is_granted("GET_FREELANCER", object)',
    uriTemplate: '/users/freelancer/{id}',
    normalizationContext: [
        'groups' => ['user_get', 'specific_freelancer_get']
    ]
)]
#[Get(
    security: 'is_granted("ROLE_ADMIN") or object == user',
    uriTemplate: '/users/{id}/reviews',
    normalizationContext: [
        'groups' => ['user_get', 'user_get_reviews']
    ]
)]
#[Get(
    security: 'is_granted("ROLE_ADMIN") or object == user',
    uriTemplate: '/users/{id}/propositions',
    normalizationContext: [
        'groups' => ['user_get', 'user_get_propositions']
    ]
)]
#[GetCollection(
    security: "is_granted('ROLE_ADMIN')",
    normalizationContext: [
        'groups' => ['user_cget']
    ]
)]
#[Put(
    security: "is_granted('ROLE_ADMIN') or object == user",
    denormalizationContext: [
        'groups' => ['user_modify']
    ],
    normalizationContext: [
        'groups' => ['user_get']
    ]
)]
#[Patch(
    uriTemplate: '/users/modify_password/{id}', 
    security: "object == user",
    denormalizationContext: [
        'groups' => ['user_modify_pwd']
    ],
    normalizationContext: [
        'groups' => ['user_get']
    ]
)]
#[Patch(
        uriTemplate: '/users/forgot_password', 
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
    uriTemplate: '/users/update_password', 
    input: UserResetPasswordDto::class,
    output: User::class,
    processor: UserResetPasswordProcessor::class,
    status: 204
)]
#[Patch(
    uriTemplate: '/users/verify_email',
    input: UserVerifyEmailDto::class,
    output: User::class,
    processor: UserVerifyEmailProcessor::class,
    status: 204
)]
#[Post(
    uriTemplate: '/register', 
    controller: RegisterController::class,
    denormalizationContext: [
        'groups' => ['user_write_register']
    ],
    normalizationContext: [
        'groups' => ['user_register']
    ]
)]
#[Post(
    uriTemplate: '/users/{id}/reviews',
    security: "is_granted('REVIEW_FREELANCER', object)",
    controller: ReviewPostController::class,
    normalizationContext: [
        'groups' => ['review_get']
    ],
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity('email', message: 'Email déjà utilisé')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups(["user_cget", "user_get", "user_write_register", "user_resetPwd", "user_resetPwd_request", "user_register", "subscription_plan_get", "subscription_get", "subscription_cget", "freelancer_info_get", "project_get_propositions", "client_info_get", "user_get_projects", "invoice_get", "project_freelancer_own", "project_full_get"])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[NotBlank()]
    #[NotNull()]
    #[Email()]
    #[Groups(["user_cget", "user_get", "user_write_register", "user_resetPwd", "user_resetPwd_request",  "user_register", "subscription_plan_get", "subscription_get", "subscription_cget", "freelancer_info_get", "project_get_propositions", "client_info_get", "user_modify", "invoice_get"])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(["user_write_register", "user_cget",  "user_get", "user_register", "client_info_get"])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    // #[Regex("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/i", message: "Must be minimum eight characters, at least one letter and one number ")]
    private ?string $password = null;

    #[Groups(["user_changePwd", "user_write_register", "user_modify_pwd"])]
    private ?string $plainPassword = null;

    #[Groups(["user_modify_pwd"])]
    private ?string $oldPassword = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["user_resetPwd", "user_changePwd"])]
    private ?string $resetPwdToken = null;

    #[ORM\Column(length: 255)]
    #[Groups(["user_get", "user_cget", "user_modify", "user_write_register", "user_register", "subscription_plan_get", "subscription_get", "subscription_cget", "freelancer_info_get", "project_get_propositions", "client_info_get", "user_get_projects", "invoice_get", "project_freelancer_own", "project_full_get"])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(["user_get", "user_cget", "user_modify", "user_write_register", "user_register", "subscription_plan_get", "subscription_get", "subscription_cget", "freelancer_info_get", "project_get_propositions", "client_info_get", "user_get_projects", "invoice_get", "project_freelancer_own", "project_full_get"])]
    private ?string $surname = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups(["user_get"])]
    private ?bool $isVerified = false;

    #[ORM\OneToOne(mappedBy: 'client', cascade: ['persist', 'remove'])]
    #[Groups(["user_get", "user_register", "specific_client_get", "project_full_get"])]
    private ?ClientInfo $clientInfo = null;

    #[ORM\OneToOne(mappedBy: 'freelancer', cascade: ['persist', 'remove'])]
    #[Groups(["user_get", "user_register", "specific_freelancer_get"])]
    private ?FreelancerInfo $freelancerInfo = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Project::class, orphanRemoval: true)]
    #[Groups(["user_get_projects"])]
    private Collection $createdProjects;

    #[ORM\OneToMany(mappedBy: 'freelancer', targetEntity: Proposition::class, orphanRemoval: true)]
    #[Groups(["user_get_propositions", "user_get"])]
    private Collection $propositions;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Invoice::class, orphanRemoval: true)]
    #[Groups(["user_get"])]
    private Collection $invoices;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: SocialLink::class, orphanRemoval: true)]
    private Collection $socialLinks;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gravatarImage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verifyEmailToken = null;

    #[ORM\OneToOne(mappedBy: 'freelancer', cascade: ['persist', 'remove'])]
    #[Groups(['user_get'])]
    private ?Subscription $subscription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeId = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $reset_pwd_token_time = null;


    #[ORM\OneToMany(mappedBy: 'freelancer', targetEntity: Review::class, orphanRemoval: true)]
    private Collection $reviews;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Review::class, orphanRemoval: true)]
    #[Groups(["user_get_reviews"])]
    private Collection $createdReviews;

    public function __construct()
    {
        $this->createdProjects = new ArrayCollection();
        $this->propositions = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->socialLinks = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->createdReviews = new ArrayCollection();
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
        foreach($roles as $role) {
            if(!in_array($role, ['ROLE_ADMIN', 'ROLE_CLIENT', 'ROLE_FREELANCER', 'ROLE_FREELANCER_PREMIUM', 'ROLE_USER'])) {
                throw new \InvalidArgumentException('Invalid role');
            }
        }
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getClientInfo(): ?ClientInfo
    {
        return $this->clientInfo;
    }

    public function setClientInfo(ClientInfo $clientInfo): self
    {
        // set the owning side of the relation if necessary
        if ($clientInfo->getClient() !== $this) {
            $clientInfo->setClient($this);
        }

        $this->clientInfo = $clientInfo;

        return $this;
    }

    public function getFreelancerInfo(): ?FreelancerInfo
    {
        return $this->freelancerInfo;
    }

    public function setFreelancerInfo(FreelancerInfo $freelancerInfo): self
    {
        // set the owning side of the relation if necessary
        if ($freelancerInfo->getFreelancer() !== $this) {
            $freelancerInfo->setFreelancer($this);
        }

        $this->freelancerInfo = $freelancerInfo;

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getCreatedProjects(): Collection
    {
        return $this->createdProjects;
    }

    public function addCreatedProject(Project $createdProject): self
    {
        if (!$this->createdProjects->contains($createdProject)) {
            $this->createdProjects[] = $createdProject;
            $createdProject->setOwner($this);
        }

        return $this;
    }

    public function removeCreatedProject(Project $createdProject): self
    {
        if ($this->createdProjects->removeElement($createdProject)) {
            // set the owning side to null (unless already changed)
            if ($createdProject->getOwner() === $this) {
                $createdProject->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Proposition>
     */
    public function getPropositions(): Collection
    {
        return $this->propositions;
    }

    public function addProposition(Proposition $proposition): self
    {
        if (!$this->propositions->contains($proposition)) {
            $this->propositions[] = $proposition;
            $proposition->setClient($this);
        }

        return $this;
    }

    public function removeProposition(Proposition $proposition): self
    {
        if ($this->propositions->removeElement($proposition)) {
            // set the owning side to null (unless already changed)
            if ($proposition->getClient() === $this) {
                $proposition->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setClient($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getClient() === $this) {
                $invoice->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SocialLink>
     */
    public function getSocialLinks(): Collection
    {
        return $this->socialLinks;
    }

    public function addSocialLink(SocialLink $socialLink): self
    {
        if (!$this->socialLinks->contains($socialLink)) {
            $this->socialLinks[] = $socialLink;
            $socialLink->setCreator($this);
        }

        return $this;
    }

    public function removeSocialLink(SocialLink $socialLink): self
    {
        if ($this->socialLinks->removeElement($socialLink)) {
            // set the owning side to null (unless already changed)
            if ($socialLink->getCreator() === $this) {
                $socialLink->setCreator(null);
            }
        }

        return $this;
    }

    public function getGravatarImage(): ?string
    {
        return $this->gravatarImage;
    }

    public function setGravatarImage(?string $gravatarImage): self
    {
        $this->gravatarImage = $gravatarImage;

        return $this;
    }

    public function getVerifyEmailToken(): ?string
    {
        return $this->verifyEmailToken;
    }

    public function setVerifyEmailToken(?string $verifyEmailToken): self
    {
        $this->verifyEmailToken = $verifyEmailToken;

        return $this;
    }
    
    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(Subscription $subscription): self
    {
        // set the owning side of the relation if necessary
        if ($subscription->getFreelancer() !== $this) {
            $subscription->setFreelancer($this);
        }

        $this->subscription = $subscription;

        return $this;
    }

    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    public function setStripeId(?string $stripeId): self
    {
        $this->stripeId = $stripeId;
        return $this;
    }
    
    public function getResetPwdTokenTime(): ?\DateTimeInterface
    {
        return $this->reset_pwd_token_time;
    }

    public function setResetPwdTokenTime(?\DateTimeInterface $reset_pwd_token_time): self
    {
        $this->reset_pwd_token_time = $reset_pwd_token_time;
        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getCreatedReviews(): Collection
    {
        return $this->createdReviews;
    }

    public function addCreatedReview(Review $review): self
    {
        if (!$this->createdReviews->contains($review)) {
            $this->createdReviews[] = $review;
            $review->setClient($this);
        }

        return $this;
    }

    public function removeCreatedReview(Review $review): self
    {
        if ($this->createdReviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getClient() === $this) {
                $review->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setFreelancer($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getFreelancer() === $this) {
                $review->setFreelancer(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\TimestampableTrait;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
#[Get(
    security: "is_granted('ROLE_ADMIN') or object.getClient() == user",
    normalizationContext: [
        'groups' => ['invoice_cget', 'invoice_get']
    ]
)]
#[GetCollection(
    security: "is_granted('ROLE_ADMIN')",
    normalizationContext: [
        'groups' => ['invoice_cget']
    ]
)]
#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    use TimestampableTrait;

    public const STATUS = [
        'CREATED' => 'CREATED',
        'PAID' => 'PAID',
        'CANCELED' => 'CANCELED',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups(['invoice_cget'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['invoice_cget'])]
    private ?float $amount = null;

    #[ORM\Column(length: 255)]
    #[Groups(['invoice_cget'])]
    private ?string $idStripe = null;

    #[ORM\Column(length: 255)]
    #[Groups(['invoice_cget'])]
    private ?string $status = null;

    #[ORM\OneToOne(inversedBy: 'invoice', cascade: ['persist', 'remove'])]
    #[Groups(['invoice_get'])]
    private ?Project $project = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['invoice_get'])]
    private ?User $client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getIdStripe(): ?string
    {
        return $this->idStripe;
    }

    public function setIdStripe(string $idStripe): self
    {
        $this->idStripe = $idStripe;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }
}

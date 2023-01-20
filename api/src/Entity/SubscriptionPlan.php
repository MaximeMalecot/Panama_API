<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use App\Repository\SubscriptionPlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
#[Get(
    security: "is_granted('ROLE_ADMIN')",
    normalizationContext: [
        'groups' => ['subscription_plan_get']
    ]
)]
#[GetCollection(
    security: "is_granted('ROLE_FREELANCER') or is_granted('ROLE_ADMIN')",
    normalizationContext: [
        'groups' => ['subscription_plan_cget']
    ]
)]
#[ORM\Entity(repositoryClass: SubscriptionPlanRepository::class)]
class SubscriptionPlan
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups(["subscription_plan_get", "subscription_plan_cget", "subscription_get", "subscription_cget"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["subscription_plan_get", "subscription_plan_cget", "subscription_get", "subscription_cget"])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["subscription_plan_get", "subscription_plan_cget"])]
    private ?string $description = null;

    #[ORM\Column(length: 7)]
    #[Groups(["subscription_plan_get", "subscription_plan_cget"])]
    private ?string $color = null;

    #[ORM\Column]
    #[Groups(["subscription_plan_get", "subscription_plan_cget", "subscription_get", "subscription_cget"])]
    private ?float $price = null;

    #[ORM\Column(length: 255)]
    #[Groups(["subscription_plan_get", "subscription_get"])]
    private ?string $stripeId = null;

    #[ORM\OneToMany(mappedBy: 'plan', targetEntity: Subscription::class, orphanRemoval: true)]
    #[Groups(["subscription_plan_get"])]
    private Collection $subscriptions;

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    public function setStripeId(string $stripeId): self
    {
        $this->stripeId = $stripeId;

        return $this;
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): self
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions[] = $subscription;
            $subscription->setPlan($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): self
    {
        if ($this->subscriptions->removeElement($subscription)) {
            // set the owning side to null (unless already changed)
            if ($subscription->getPlan() === $this) {
                $subscription->setPlan(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\TimestampableTrait;
use App\Repository\PropositionRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
#[Get(
    security: "is_granted('ROLE_ADMIN') or object.getFreelancer() === user",
    normalizationContext: [
        "groups" => ["proposition_get"]
    ]
)]
#[GetCollection(
    security: "is_granted('ROLE_ADMIN')",
    normalizationContext: [
        "groups" => ["proposition_cget"]
    ]
)]
#[ORM\Entity(repositoryClass: PropositionRepository::class)]
class Proposition
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups(["project_get_propositions", "proposition_get", "proposition_cget"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["project_get_propositions", "proposition_get", "proposition_cget"])]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: "propositions")]
    #[Groups(["proposition_get", "proposition_cget"])]
    private ?Project $project = null;

    #[ORM\ManyToOne(inversedBy: "propositions")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["project_get_propositions", "proposition_get"])]
    private ?User $freelancer = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getFreelancer(): ?User
    {
        return $this->freelancer;
    }

    public function setFreelancer(?User $freelancer): self
    {
        $this->freelancer = $freelancer;

        return $this;
    }
}

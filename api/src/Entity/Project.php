<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProjectRepository;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
#[Get(
    security: "is_granted('ROLE_FREELANCER')",
    normalizationContext: [
        'groups' => ['project_get']
    ]
)]
#[Get(
    name: 'get_project_by_owner',
    path: '/projects/owner',
    security: "object.getOwner() === user",
    normalizationContext: [
        'groups' => ['project_get_own']
    ]
)]
#[GetCollection(
    security: "is_granted('ROLE_FREELANCER')",
    normalizationContext: [
        'groups' => ['project_cget']
    ]
)]
#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    use TimestampableTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups(["project_get", "project_cget", "project_get_own"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["project_get", "project_cget", "project_get_own"])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["project_get", "project_get_own"])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'createdProjects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\ManyToMany(targetEntity: Filter::class, mappedBy: 'projects')]
    #[Groups(["project_get", "project_cget", "project_get_own"])]
    private Collection $filters;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Proposition::class)]
    #[Groups(["project_get_own"])]
    private Collection $propositions;

    #[ORM\OneToOne(mappedBy: 'project', cascade: ['persist', 'remove'])]
    #[Groups(["project_get_own"])]
    private ?Invoice $invoice = null;

    #[ORM\Column]
    #[Groups(["project_get", "project_get_own"])]
    private ?int $minPrice = null;

    #[ORM\Column]
    #[Groups(["project_get", "project_get_own"])]
    private ?int $maxPrice = null;

    #[ORM\Column(length: 255, options: ['default' => 'CREATED'])]
    #[Groups(["project_get_own"])]
    private ?string $status = "CREATED";

    #[ORM\Column]
    #[Groups(["project_get", "project_get_own"])]
    private ?int $length = null;

    public function __construct()
    {
        $this->filters = new ArrayCollection();
        $this->propositions = new ArrayCollection();
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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, Filter>
     */
    public function getFilters(): Collection
    {
        return $this->filters;
    }

    public function addFilter(Filter $filter): self
    {
        if (!$this->filters->contains($filter)) {
            $this->filters[] = $filter;
            $filter->addProject($this);
        }

        return $this;
    }

    public function removeFilter(Filter $filter): self
    {
        if ($this->filters->removeElement($filter)) {
            $filter->removeProject($this);
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
            $proposition->setProject($this);
        }

        return $this;
    }

    public function removeProposition(Proposition $proposition): self
    {
        if ($this->propositions->removeElement($proposition)) {
            // set the owning side to null (unless already changed)
            if ($proposition->getProject() === $this) {
                $proposition->setProject(null);
            }
        }

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(Invoice $invoice): self
    {
        // set the owning side of the relation if necessary
        if ($invoice->getProject() !== $this) {
            $invoice->setProject($this);
        }

        $this->invoice = $invoice;

        return $this;
    }

    public function getMinPrice(): ?float
    {
        return $this->minPrice;
    }

    public function setMinPrice(float $minPrice): self
    {
        $this->minPrice = $minPrice;

        return $this;
    }

    public function getMaxPrice(): ?int
    {
        return $this->maxPrice;
    }

    public function setMaxPrice(int $maxPrice): self
    {
        $this->maxPrice = $maxPrice;

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

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }
}

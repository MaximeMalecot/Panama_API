<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProjectRepository;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\ProjetCloseController;
use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use App\Controller\PropositionPostController;
use App\Controller\PropositionGetOwnController;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\NumericFilter;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial', 'status' => 'exact', 'filters.name' => 'exact' ])]
#[ApiFilter(RangeFilter::class, properties: ['maxPrice', 'minPrice'])]
#[ApiFilter(NumericFilter::class, properties: ['length'])]
#[Get(
    security: "is_granted('ROLE_FREELANCER') or object.getOwner() === user",
    normalizationContext: [
        'groups' => ['project_get']
    ]
)]
#[Get(
    uriTemplate: '/projects/{id}/full',
    security: "is_granted('ROLE_FREELANCER_PREMIUM')",
    normalizationContext: [
        'groups' => ['project_get', 'project_full_get']
    ]
)]
#[Get(
    uriTemplate: '/projects/{id}/propositions',
    security: "is_granted('ROLE_ADMIN') or object.getOwner() === user",
    normalizationContext: [
        'groups' => ['project_get', 'project_get_propositions', 'timestamp']
    ]
)]
#[Get(
    uriTemplate: '/projects/{id}/own',
    controller: PropositionGetOwnController::class,
    security: "is_granted('ROLE_FREELANCER_PREMIUM')",
)]
#[GetCollection(
    normalizationContext: [
        'groups' => ['project_cget']
    ]
)]
#[Patch(
    security: "(is_granted('ROLE_ADMIN') or object.getOwner() === user) and object.getStatus() === 'ACTIVE'",
    normalizationContext: [
        'groups' => ['project_get', 'project_get_propositions', 'timestamp']
    ],
    denormalizationContext: [
        'groups' => ['project_patch']
    ]
)]
#[Post(
    uriTemplate: "/projects/{id}/propositions",
    controller: PropositionPostController::class,
    security: "is_granted('CREATE_PROPOSITION', object)",
)]
#[Patch(
    uriTemplate: "/projects/{id}/close",
    controller: ProjetCloseController::class,
    security: "object.getStatus() === 'IN_PROGRESS' and (is_granted('ROLE_ADMIN') or object.getOwner() == user)",
)]
#[Delete(
    security: "is_granted('DELETE_PROJECT', object)",
)]
#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    use TimestampableTrait;
    
    public const STATUS = [
        'CREATED' => 'CREATED',
        'ACTIVE' => 'ACTIVE',
        'CANCELED' => 'CANCELED',
        'IN_PROGRESS' => 'IN_PROGRESS',
        'ENDED' => 'ENDED',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups(["project_get", "project_cget", "project_get_propositions", "proposition_cget", "user_get_projects", "user_get_propositions", "invoice_get", "project_freelancer_own"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["project_get", "project_cget", "project_get_propositions", "proposition_cget", "user_get_projects", "user_get_propositions", "project_patch", "invoice_get", "project_freelancer_own", 'user_get'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["project_get", "project_get_propositions", "user_get_projects", "user_get_propositions", "project_patch", "invoice_get", "project_freelancer_own"])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'createdProjects')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["project_freelancer_own", "project_full_get"])]
    private ?User $owner = null;

    #[ORM\ManyToMany(targetEntity: Filter::class, mappedBy: 'projects', cascade: ['persist'])]
    #[Groups(["project_get", "project_cget", "project_get_propositions", "user_get_projects", "project_patch", "project_freelancer_own"])]
    private Collection $filters;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Proposition::class, orphanRemoval: true)]
    #[Groups(["project_get_own", "project_get_propositions", "user_get_projects"])]
    private Collection $propositions;

    #[ORM\OneToOne(mappedBy: 'project', cascade: ['persist', 'remove'])]
    #[Groups(["project_get_own"])]
    private ?Invoice $invoice = null;

    #[ORM\Column]
    #[Groups(["project_get", "project_cget", "project_get_propositions", "proposition_cget", "user_get_projects", "project_freelancer_own"])]
    private ?int $minPrice = null;

    #[ORM\Column]
    #[Groups(["project_get",  "project_cget", "project_get_propositions", "proposition_cget", "user_get_projects", "project_freelancer_own"])]
    private ?int $maxPrice = null;

    #[ORM\Column(length: 255, options: ['default' => 'CREATED'])]
    #[Groups(["project_get", "project_get_own", "project_get_propositions", "user_get_projects", "project_cget", "project_freelancer_own"])]
    private ?string $status = "CREATED";

    #[ORM\Column]
    #[Groups(["project_get",  "project_cget", "project_get_propositions", "proposition_cget", "user_get_projects", "project_freelancer_own"])]
    private ?int $length = null;
    
    #[ORM\Column(length: 128, unique: true)]
    #[Slug(fields: ['id', 'name'])]
    private $slug;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }
}

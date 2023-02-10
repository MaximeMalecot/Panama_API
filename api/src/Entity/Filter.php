<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\FilterRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
#[ApiFilter(SearchFilter::class,properties: ['name' => 'partial','type' => 'exact'])]
#[Get(
    security: "is_granted('ROLE_FREELANCER') or is_granted('ROLE_CLIENT')",
    normalizationContext: [
        'groups' => ['filter_get']
    ]
)]
#[GetCollection(
    // security: "is_granted('ROLE_FREELANCER') or is_granted('ROLE_CLIENT')",
    normalizationContext: [
        'groups' => ['filter_cget']
    ]
)]
#[Post(
    security: "is_granted('ROLE_ADMIN')",
    normalizationContext: [
        'groups' => ['filter_cget']
    ],
    denormalizationContext: [
        'groups' => ['filter_post']
    ]
)]
#[Patch(
    security: "is_granted('ROLE_ADMIN')",
    normalizationContext: [
        'groups' => ['filter_cget']
    ],
    denormalizationContext: [
        'groups' => ['filter_post']
    ]
)]
#[Delete(
    security: "is_granted('ROLE_ADMIN')",
)]
#[ORM\Entity(repositoryClass: FilterRepository::class)]
class Filter
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups(["filter_get", "filter_cget"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["filter_get", "filter_cget", "filter_post", "project_cget", "project_get", "project_freelancer_own"])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["filter_get", "filter_cget", "filter_post", "project_freelancer_own"])]
    private ?string $type = null;

    #[ORM\ManyToMany(targetEntity: Project::class, inversedBy: 'filters', cascade: ['persist'])]
    #[Groups(["filter_get"])]
    private Collection $projects;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        if(!in_array($type, ["techno", "other"])){
            $type = "other";
        }
        $this->type = $type;
        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        $this->projects->removeElement($project);

        return $this;
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\FilterRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
#[Get(
    normalizationContext: [
        'groups' => ['fitler_get']
    ]
)]
#[ApiFilter(SearchFilter::class,properties: ['name' => 'exact','type' => 'exact'])]
#[GetCollection(
    normalizationContext: [
        'groups' => ['fitler_cget']
    ]
)]
#[ORM\Entity(repositoryClass: FilterRepository::class)]
class Filter
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups(["fitler_get", "fitler_cget"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["fitler_get", "fitler_cget"])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["fitler_get", "fitler_cget"])]
    private ?string $type = null;

    #[ORM\ManyToMany(targetEntity: Project::class, inversedBy: 'filters')]
    #[Groups(["fitler_get"])]
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

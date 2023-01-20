<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReviewRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\TimestampableTrait;

#[ApiResource]
#[Get(
    normalizationContext: [
        'groups' => ['review_get']
    ]
)]
#[GetCollection(
    normalizationContext: [
        'groups' => ['review_cget']
    ]
)]
#[Post(
    security: "is_granted('ROLE_CLIENT')",
    normalizationContext: [
        'groups' => ['review_get']
    ],
    denormalizationContext: [
        'groups' => ['review_post']
    ]
)]
#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    use TimestampableTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups(["review_get", "review_cget"])]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\Range(
        min: 0,
        max: 10,
        notInRangeMessage: 'The must must be a float between {{ min }} and {{ max }}',
    )]
    #[Groups(["review_get", "review_cget", "review_post"])]
    private ?float $mark = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["review_get", "review_post"])]
    private ?string $content = null;

    #[ORM\OneToOne(inversedBy: 'review', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FreelancerInfo $freelancer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMark(): ?float
    {
        return $this->mark;
    }

    public function setMark(float $mark): self
    {
        $this->mark = $mark;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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

    public function getFreelancer(): ?FreelancerInfo
    {
        return $this->freelancer;
    }

    public function setFreelancer(?FreelancerInfo $freelancer): self
    {
        $this->freelancer = $freelancer;

        return $this;
    }
}

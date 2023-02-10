<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReviewRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\ReviewPostController;
use App\Entity\Traits\TimestampableTrait;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

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
#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    use TimestampableTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups(["review_get", "review_cget"])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(
        min: 0,
        max: 10,
        notInRangeMessage: 'The must must be a float between {{ min }} and {{ max }}',
    )]
    #[Groups(["review_get", "review_cget"])]
    private ?float $mark = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["review_get"])]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["review_get", "review_cget"])]
    private ?User $freelancer = null;

    #[ORM\ManyToOne(inversedBy: 'createdReviews')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["review_get", "review_cget"])]
    private ?User $client = null;

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

    public function getFreelancer(): ?User
    {
        return $this->freelancer;
    }

    public function setFreelancer(?User $freelancer): self
    {
        $this->freelancer = $freelancer;

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
}

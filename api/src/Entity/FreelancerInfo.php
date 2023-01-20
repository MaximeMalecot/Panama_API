<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Dto\FreelancerInfoKYCDto;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\FreelancerInfoKYCProcessor;
use App\Repository\FreelancerInfoRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
#[Get(
    security: "is_granted('ROLE_ADMIN') or object.getFreelancer() == user",
    normalizationContext: [
        'groups' => ['freelancer_info_get']
    ]
)]
#[GetCollection(
    security: "is_granted('ROLE_ADMIN')",
    normalizationContext: [
        'groups' => ['freelancer_info_cget']
    ]
)]
#[Post(
    security: "is_granted('FREELANCER_VERIFY')",
    name: 'freelancer_info_kyc',
    uriTemplate: '/freelancer_info/kyc', 
    input: FreelancerInfoKYCDto::class, 
    processor: FreelancerInfoKYCProcessor::class,
    status: 204
)]
#[ORM\Entity(repositoryClass: FreelancerInfoRepository::class)]
class FreelancerInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups(["freelancer_info_get", "freelancer_info_cget", "specific_freelancer_get"])]
    private ?int $id = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups(["freelancer_info_get", "freelancer_info_cget"])]
    private ?bool $isVerified = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["freelancer_info_get", "freelancer_info_cget",  "specific_freelancer_get"])]
    private ?string $description = null;

    #[ORM\Column(length: 12, nullable: true)]
    #[Groups(["freelancer_info_get", "freelancer_info_cget",  "specific_freelancer_get"])]
    private ?string $phoneNb = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["freelancer_info_get", "freelancer_info_cget",  "specific_freelancer_get"])]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["freelancer_info_get", "freelancer_info_cget",  "specific_freelancer_get"])]
    private ?string $city = null;

    #[ORM\OneToOne(inversedBy: 'freelancerInfo', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["freelancer_info_get", "freelancer_info_cget"])]
    private ?User $freelancer = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPhoneNb(): ?string
    {
        return $this->phoneNb;
    }

    public function setPhoneNb(string $phoneNb): self
    {
        $this->phoneNb = $phoneNb;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getFreelancer(): ?User
    {
        return $this->freelancer;
    }

    public function setFreelancer(User $freelancer): self
    {
        $this->freelancer = $freelancer;

        return $this;
    }
}

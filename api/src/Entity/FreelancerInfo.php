<?php

namespace App\Entity;

use App\Repository\FreelancerInfoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FreelancerInfoRepository::class)]
class FreelancerInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $isVerified = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 12)]
    private ?string $phoneNb = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\OneToOne(inversedBy: 'freelancerInfo', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $freelancer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isIsVerified(): ?bool
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
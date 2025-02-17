<?php

namespace App\Entity;

use App\Repository\AchievementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AchievementRepository::class)]
class Achievement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @var int|null */
    private ?int $id = null; // Keep this as is, it's necessary for Doctrine

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $achievedAt = null;

    #[ORM\ManyToOne(inversedBy: 'achievements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User  $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getAchievedAt(): ?\DateTimeImmutable
    {
        return $this->achievedAt;
    }

    public function setAchievedAt(\DateTimeImmutable $achievedAt): static
    {
        $this->achievedAt = $achievedAt;
        return $this;
    }

    public function getUser (): ?User 
    {
        return $this->user;
    }

    public function setUser (?User  $user): static
    {
        $this->user = $user;
        return $this;
    }
}
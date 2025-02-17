<?php

namespace App\Entity;

use App\Repository\FriendRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FriendRequestRepository::class)]
class FriendRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @var int|null */
    private ?int $id = null; // Keep this as is, it's necessary for Doctrine

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User  $fromUser  = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User  $toUser  = null;

    #[ORM\Column(length: 10)]
    private string $status = 'pending';  // Possible values: 'pending', 'accepted', 'declined'

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromUser (): ?User 
    {
        return $this->fromUser ;
    }

    public function setFromUser (?User  $fromUser ): static
    {
        $this->fromUser  = $fromUser ;
        return $this;
    }

    public function getToUser (): ?User 
    {
        return $this->toUser ;
    }

    public function setToUser (?User  $toUser ): static
    {
        $this->toUser  = $toUser ;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }
}
<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $sender = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $receiver = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Message $parentMessage = null;

    #[ORM\Column(type: 'boolean')]
    private bool $read = false;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $deletedBySender = null; // FIXED: Must be null by default

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Game $gameChallenge = null;

    // Getters & Setters

    public function getGameChallenge(): ?Game
    {
        return $this->gameChallenge;
    }

    public function setGameChallenge(?Game $gameChallenge): static
    {
        $this->gameChallenge = $gameChallenge;
        return $this;
    }

    public function getDeletedBySender(): ?bool
    {
        return $this->deletedBySender;
    }

    public function setDeletedBySender(?bool $deletedBySender): static
    {
        $this->deletedBySender = $deletedBySender;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->read;
    }

    public function setRead(bool $read): static
    {
        $this->read = $read;
        return $this;
    }

    public function getParentMessage(): ?Message
    {
        return $this->parentMessage;
    }

    public function setParentMessage(?Message $parentMessage): static
    {
        $this->parentMessage = $parentMessage;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;
        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(?User $receiver): static
    {
        $this->receiver = $receiver;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;
        return $this;
    }
}

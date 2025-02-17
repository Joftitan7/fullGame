<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['username'], message: 'This username is already taken')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank]
    private ?string $familyName = null;

    #[ORM\Column(type: 'string', length: 20, unique: true, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: "/^\+?\d{10,15}$/", message: "Invalid phone number.")]
    private ?string $phone = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $isAdmin = false;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6, max: 255)]
    private ?string $plainPassword = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $profilePhoto = null;

    #[ORM\OneToMany(mappedBy: 'fromUser', targetEntity: FriendRequest::class, cascade: ['remove'])]
    private Collection $sentFriendRequests;

    #[ORM\OneToMany(mappedBy: 'toUser', targetEntity: FriendRequest::class, cascade: ['remove'])]
    private Collection $receivedFriendRequests;

    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $games;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'sender', orphanRemoval: true)]
    private Collection $messages;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'friendsWithMe')]
    private Collection $myFriends;

    #[ORM\ManyToMany(targetEntity: self::class)]
    #[ORM\JoinTable(name: 'user_friends')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'friend_id', referencedColumnName: 'id')]
    private Collection $friendsWithMe;

    /**
     * @var Collection<int, Achievement>
     */
    #[ORM\OneToMany(targetEntity: Achievement::class, mappedBy: 'user')]
    private Collection $achievements;

    // #[ORM\Column(type: 'integer', nullable: true)]
    // private ?int $stepsNormal = null;

    // #[ORM\Column(type: 'integer', nullable: true)]
    // private ?int $stepsHard = null;

    // #[ORM\Column(type: 'integer', nullable: true)]
    // private ?int $stepsExtreme = null;

    #[ORM\Column(nullable: true)]
    private ?int $stepsForNormal = null;
    
    #[ORM\Column(nullable: true)]
    private ?int $stepsForHard = null;
    
    #[ORM\Column(nullable: true)]
    private ?int $stepsForExtreme = null;





    #[ORM\ManyToMany(targetEntity: self::class)]
#[ORM\JoinTable(name: 'user_friends')]
#[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
#[ORM\InverseJoinColumn(name: 'friend_id', referencedColumnName: 'id')]
private Collection $friends; // Renamed from myFriends and friendsWithMe


// Get all friends (mutual)
public function getFriends(): Collection
{
    return $this->friends;
}

// Add a friend
public function addFriend(self $friend): static
{
    if (!$this->friends->contains($friend)) {
        $this->friends->add($friend);
        $friend->addFriend($this);  // Ensure the reverse relation is updated
    }

    return $this;
}

// Remove a friend
public function removeFriend(self $friend): static
{
    if ($this->friends->contains($friend)) {
        $this->friends->removeElement($friend);
        $friend->removeFriend($this);  // Ensure the reverse relation is updated
    }

    return $this;
}




    // Getters and Setters for the new fields
    public function getStepsNormal(): ?int
    {
        return $this->stepsNormal;
    }

    public function setStepsNormal(?int $stepsNormal): static
    {
        $this->stepsNormal = $stepsNormal;
        return $this;
    }

    public function getStepsHard(): ?int
    {
        return $this->stepsHard;
    }

    public function setStepsHard(?int $stepsHard): static
    {
        $this->stepsHard = $stepsHard;
        return $this;
    }

    public function getStepsExtreme(): ?int
    {
        return $this->stepsExtreme;
    }

    public function setStepsExtreme(?int $stepsExtreme): static
    {
        $this->stepsExtreme = $stepsExtreme;
        return $this;
    }

    // ✅ Get all friends (mutual)
    // public function getFriends(): Collection
    // {
    //     return new ArrayCollection(
    //         array_merge($this->myFriends->toArray(), $this->friendsWithMe->toArray())
    //     );
    // }

    // ✅ Add a friend
    // public function addFriend(self $friend): static
    // {
    //     if (!$this->myFriends->contains($friend)) {
    //         $this->myFriends->add($friend);
    //         $friend->friendsWithMe->add($this);
    //     }
    //     return $this;
    // }

    // ✅ Remove a friend
    // public function removeFriend(self $friend): static
    // {
    //     if ($this->myFriends->contains($friend)) {
    //         $this->myFriends->removeElement($friend);
    //         $friend->friendsWithMe->removeElement($this);
    //     }
    //     return $this;
    // }


    public function __construct()
    {
        $this->games = new ArrayCollection();
        $this->sentFriendRequests = new ArrayCollection();
        $this->receivedFriendRequests = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->myFriends = new ArrayCollection();
        $this->friendsWithMe = new ArrayCollection();
        $this->achievements = new ArrayCollection();
        $this->friends = new ArrayCollection();
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(string $familyName): self
    {
        $this->familyName = $familyName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function isIsAdmin(): ?bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): static
    {
        $this->isAdmin = $isAdmin;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email; // Change from email to username
    }

    public function getProfilePhoto(): ?string
    {
        return $this->profilePhoto;
    }

    public function setProfilePhoto(?string $profilePhoto): self
    {
        $this->profilePhoto = $profilePhoto;
        return $this;
    }

    public function getSentFriendRequests(): Collection
    {
        return $this->sentFriendRequests;
    }

    public function getReceivedFriendRequests(): Collection
    {
        return $this->receivedFriendRequests;
    }

    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): static
    {
        if (!$this->games->contains($game)) {
            $this->games->add($game);
            $game->setUser($this);
        }
        return $this;
    }

    public function removeGame(Game $game): static
    {
        if ($this->games->removeElement($game)) {
            if ($game->getUser() === $this) {
                $game->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setSender($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Achievement>
     */
    public function getAchievements(): Collection
    {
        return $this->achievements;
    }

    public function addAchievement(Achievement $achievement): static
    {
        if (!$this->achievements->contains($achievement)) {
            $this->achievements->add($achievement);
            $achievement->setUser($this);
        }

        return $this;
    }

    public function removeAchievement(Achievement $achievement): static
    {
        if ($this->achievements->removeElement($achievement)) {
            // set the owning side to null (unless already changed)
            if ($achievement->getUser() === $this) {
                $achievement->setUser(null);
            }
        }

        return $this;
    }

    public function getStepsForNormal(): ?int
    {
        return $this->stepsForNormal;
    }

    public function setStepsForNormal(int $stepsForNormal): static
    {
        $this->stepsForNormal = $stepsForNormal;

        return $this;
    }

    public function getStepsForHard(): ?int
    {
        return $this->stepsForHard;
    }

    public function setStepsForHard(int $stepsForHard): static
    {
        $this->stepsForHard = $stepsForHard;

        return $this;
    }

    public function getStepsForExtreme(): ?int
    {
        return $this->stepsForExtreme;
    }

    public function setStepsForExtreme(int $stepsForExtreme): static
    {
        $this->stepsForExtreme = $stepsForExtreme;

        return $this;
    }
}

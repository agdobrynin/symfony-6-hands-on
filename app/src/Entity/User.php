<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToOne(mappedBy: 'toUser', cascade: ['persist', 'remove'])]
    private ?UserProfile $userProfile = null;

    #[ORM\ManyToMany(targetEntity: MicroPost::class, mappedBy: 'likedBy')]
    private Collection $likedPosts;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: MicroPost::class)]
    private Collection $microPosts;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Comment::class)]
    private Collection $comments;

    public function __construct()
    {
        $this->likedPosts = new ArrayCollection();
        $this->microPosts = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUserProfile(): ?UserProfile
    {
        return $this->userProfile;
    }

    public function setUserProfile(UserProfile $userProfile): self
    {
        // set the owning side of the relation if necessary
        if ($userProfile->getToUser() !== $this) {
            $userProfile->setToUser($this);
        }

        $this->userProfile = $userProfile;

        return $this;
    }

    /**
     * @return Collection<int, MicroPost>
     */
    public function getLikedPosts(): Collection
    {
        return $this->likedPosts;
    }

    public function addLikedPost(MicroPost $likedPost): self
    {
        if (!$this->likedPosts->contains($likedPost)) {
            $this->likedPosts->add($likedPost);
            $likedPost->addLikedBy($this);
        }

        return $this;
    }

    public function removeLikedPost(MicroPost $likedPost): self
    {
        if ($this->likedPosts->removeElement($likedPost)) {
            $likedPost->removeLikedBy($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, MicroPost>
     */
    public function getMicroPosts(): Collection
    {
        return $this->microPosts;
    }

    public function addMicroPost(MicroPost $microPost): self
    {
        if (!$this->microPosts->contains($microPost)) {
            $this->microPosts->add($microPost);
            $microPost->setAuthor($this);
        }

        return $this;
    }

    public function removeMicroPost(MicroPost $microPost): self
    {
        if ($this->microPosts->removeElement($microPost)) {
            // set the owning side to null (unless already changed)
            if ($microPost->getAuthor() === $this) {
                $microPost->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }
}

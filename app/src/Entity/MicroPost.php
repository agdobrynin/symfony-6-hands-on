<?php

namespace App\Entity;

use App\Repository\MicroPostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: MicroPostRepository::class)]
#[ORM\HasLifecycleCallbacks]
class MicroPost
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private readonly ?Ulid $id;

    #[ORM\Column(length: 100, nullable: false)]
    private string $title;

    #[ORM\Column(length: 300, nullable: false)]
    private string $content;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updateAt = null;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->updateAt;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updateAt = new \DateTimeImmutable();
    }
}

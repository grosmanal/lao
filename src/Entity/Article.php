<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 */
#[ApiResource(
    security: "is_granted('ROLE_ADMIN')",
    collectionOperations: [
        'get',
        'post' => [
            'denormalization_context' => [
                'groups' => [ 'article:post' ],
                'datetime_format' => 'Y-m-d H:i:s'
            ],
        ],
    ],
    itemOperations: [
        'get',
        'put' => [
            'denormalization_context' => [ 'datetime_format' => 'Y-m-d H:i:s' ],
        ],
        'delete',
    ],
)]
class Article
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    #[Groups(['article:post'])]
    #[Assert\Type(\DateTimeImmutable::class)]
    private $publishFrom;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    #[Groups(['article:post'])]
    #[Assert\Type(\DateTimeImmutable::class)]
    private $publishTo;

    /**
     * @ORM\Column(type="text")
     */
    #[Groups(['article:post'])]
    #[Assert\NotBlank()]
    private $content;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    #[Groups(['article:post'])]
    #[Assert\Choice(['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'])]
    private $style;

    /**
     * @ORM\ManyToMany(targetEntity=Doctor::class)
     */
    private $readByDoctors;

    public function __construct()
    {
        $this->readByDoctors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublishFrom(): ?\DateTimeImmutable
    {
        return $this->publishFrom;
    }

    public function setPublishFrom(?\DateTimeImmutable $publishFrom): self
    {
        $this->publishFrom = $publishFrom;

        return $this;
    }

    public function getPublishTo(): ?\DateTimeImmutable
    {
        return $this->publishTo;
    }

    public function setPublishTo(?\DateTimeImmutable $publishTo): self
    {
        $this->publishTo = $publishTo;

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

    public function getStyle(): ?string
    {
        return $this->style;
    }

    public function setStyle(?string $style): self
    {
        $this->style = $style;

        return $this;
    }

    /**
     * @return Collection|Doctor[]
     */
    public function getReadByDoctors(): Collection
    {
        return $this->readByDoctors;
    }

    public function addReadByDoctor(Doctor $readByDoctor): self
    {
        if (!$this->readByDoctors->contains($readByDoctor)) {
            $this->readByDoctors[] = $readByDoctor;
        }

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function removeReadByDoctor(Doctor $readByDoctor): self
    {
        $this->readByDoctors->removeElement($readByDoctor);

        return $this;
    }
}

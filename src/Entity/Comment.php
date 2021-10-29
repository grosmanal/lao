<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
#[ApiResource(
    normalizationContext: ['groups' => ['comment:read']],
    collectionOperations: [
        'get',
        'post' => ['security_post_denormalize' => "is_granted('edit', object)"],
    ],
    itemOperations: [
        'get' => ['security' => "is_granted('view', object)"],
        'put' => [
            'security' => "is_granted('edit', object)",
            'denormalization_context' => ['groups' => ['comment:put']],
        ],
        'delete' => ['security' => "is_granted('edit', object)"],
    ],
)]
class Comment implements OfficeOwnedInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['comment:read'])]
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Doctor::class)
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    #[Groups(['comment:read'])]
    private $author;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Assert\NotBlank
     */
    #[Groups(['comment:read'])]
    private $creationDate;

    /**
     * @ORM\ManyToOne(targetEntity=CareRequest::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    #[Groups(['comment:read'])]
    private $careRequest;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotBlank
     */
    #[Groups(['comment:read', 'comment:put'])]
    private $content;

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        // Cohérence office. Le cabinet de la care_request le même que :
        // - l'auteur
        if ($this->getAuthor()) {
            if ($this->getOffice() != $this->getAuthor()->getOffice()) {
                $context
                    ->buildViolation('Author office mismatch care request’s one')
                    ->atPath('author')
                    ->addViolation()
                    ;
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?Doctor
    {
        return $this->author;
    }

    public function setAuthor(?Doctor $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeImmutable
    {
        return $this->creationDate;
    }
    
    public function getCreationDateNonImmutable(): ?\DateTime
    {
        return \DateTime::createFromImmutable($this->creationDate);
    }

    public function setCreationDate(\DateTimeImmutable $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getCareRequest(): ?CareRequest
    {
        return $this->careRequest;
    }

    public function setCareRequest(?CareRequest $careRequest): self
    {
        $this->careRequest = $careRequest;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }
    
    public function getOffice(): ?Office
    {
        if (!$this->getCareRequest()) {
            return null;
        }

        return $this->getCareRequest()->getOffice();
    }
}

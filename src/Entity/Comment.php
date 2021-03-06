<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
#[ApiResource(
    normalizationContext: ['groups' => ['comment:read']],
    collectionOperations: [
        'get',
        'post' => [
            'denormalization_context' => ['groups' => ['comment:post']],
            'security_post_denormalize' => "is_granted('edit', object)"
        ],
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
class Comment implements DoctorOwnedInterface, OfficeOwnedInterface, ActivityLoggableEntityInterface
{
    use ActivityLoggableTrait;

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
    #[Groups(['comment:read', 'comment:post'])]
    private $author;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $modifiedAt;

    /**
     * @ORM\ManyToOne(targetEntity=CareRequest::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    #[Groups(['comment:read', 'comment:post'])]
    private $careRequest;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotBlank
     */
    #[Groups(['comment:read', 'comment:post', 'comment:put'])]
    private $content;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="comment", orphanRemoval=true, cascade={"persist"})
     */
    private $notifications;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        // Coh??rence office. Le cabinet de la care_request doit ??tre le m??me que :
        // - l'auteur
        if ($this->getAuthor()) {
            if ($this->getOffice() != $this->getAuthor()->getOffice()) {
                $context
                    ->buildViolation('Author office mismatch care request???s one')
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

    public function getCreatedBy(): ?User
    {
        return $this->author;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->author = $createdBy;

        return $this;
    }

    public function getModifiedBy(): ?User
    {
        return $this->author;
    }

    public function setModifiedBy(?User $modifiedBy): self
    {
        // Rien ?? faire : seul l'auteur d'un commentaire peut le modifier (DoctorOwnedInterface)
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

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setComment($this);
        }

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getComment() === $this) {
                $notification->setComment(null);
            }
        }

        return $this;
    }

    public function ownedByDoctor(): ?Doctor
    {
        return $this->getAuthor();
    }

    public function ownedByOffice(): ?Office
    {
        return $this->getOffice();
    }

    public function getActivityObjectName(): string
    {
        return $this->getCareRequest()->getPatient()->getDisplayName();
    }

    public function getActivityIcon(): string
    {
        return 'bi-chat-left-text';
    }

    public function getActivityRoute(): array
    {
        return [
            'name' => 'patient',
            'parameters' => [
                'id' => $this->getCareRequest()->getPatient()->getId(),
                '_fragment' => sprintf('comment-%d', $this->getId()),
            ],
        ];
    }

    public function getActivityMessage(string $action): TranslatableMessage
    {
        return new TranslatableMessage(sprintf('activity.comment.%s', $action), [
            '%careRequestCreationDate%' => $this->getCareRequest()->getCreatedAt()->format('d/m/Y')
        ]);
    }
}

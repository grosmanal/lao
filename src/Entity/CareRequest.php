<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CareRequestRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=CareRequestRepository::class)
 */
#[ApiResource(
    normalizationContext: ['groups' => ['careRequest:read']],
    collectionOperations: [
        'get',
        'post' => ['security_post_denormalize' => "is_granted('edit', object)"],
    ],
    itemOperations: [
        'get' => ['security' => "is_granted('view', object)"],
        'delete' => ['security' => "is_granted('edit', object)"],
        'put' => [
            'security' => "is_granted('edit', object)",
            'denormalization_context' => ['groups' => ['careRequest:put']],
        ],
    ],
)]
class CareRequest implements OfficeOwnedInterface, ActivityLoggableEntityInterface
{
    use ActivityLoggableTrait;

    const STATE_NEW = 'new';
    const STATE_ACTIVE = 'active';
    const STATE_ARCHIVED = 'archived';
    const STATE_ABANDONED = 'abandoned';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Patient::class, inversedBy="careRequests")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    #[Groups(['careRequest:read', 'comment:read'])]
    private $patient;

    /**
     * @ORM\ManyToOne(targetEntity=Doctor::class)
     * @Assert\NotBlank
     */
    #[Groups(['careRequest:read', 'careRequest:put'])]
    private $contactedBy;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Assert\NotBlank
     */
    #[Groups(['careRequest:read', 'careRequest:put', 'comment:read'])]
    private $contactedAt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    #[Groups(['careRequest:read', 'careRequest:put'])]
    private $priority;

    /**
     * @ORM\ManyToOne(targetEntity=Complaint::class)
     */
    #[Groups(['careRequest:read', 'careRequest:put'])]
    private $complaint;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups(['careRequest:read', 'careRequest:put'])]
    private $customComplaint;

    /**
     * @ORM\ManyToOne(targetEntity=Doctor::class)
     */
    #[Groups(['careRequest:read', 'careRequest:put'])]
    private $acceptedBy;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    #[Groups(['careRequest:read', 'careRequest:put'])]
    private $acceptedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Doctor::class)
     */
    #[Groups(['careRequest:read', 'careRequest:put'])]
    private $abandonedBy;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    #[Groups(['careRequest:read', 'careRequest:put'])]
    private $abandonedAt;

    /**
     * @ORM\ManyToOne(targetEntity=AbandonReason::class)
     */
    #[Groups(['careRequest:read', 'careRequest:put'])]
    private $abandonedReason;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="careRequest", orphanRemoval=true, cascade={"remove"})
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    #[ApiSubresource()]
    private $comments;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $modifiedBy;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $modifiedAt;

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        // Une demande ne peut pas être à la fois abandonnée et archivée (acceptée)
        if ($this->getAcceptedAt() != null && $this->getAbandonedAt() != null) {
            $context
                ->buildViolation('care_request.error.both_accepted_abandonned')
                ->setTranslationDomain('messages')
                ->atPath('acceptedAt')
                ->addViolation()
                ;
        }
        
        // Cohérence office. Le cabinet du patient doit être le même que :
        // - le docteur contacté
        if ($this->getContactedBy()) {
            if ($this->getOffice() != $this->getContactedBy()->getOffice()) {
                $context
                    ->buildViolation('care_request.error.contacting_doctor_office')
                    ->setTranslationDomain('messages')
                    ->atPath('contactedBy')
                    ->addViolation()
                    ;
            }
        }

        // - le docteur prenant en charge
        if ($this->getAcceptedBy()) {
            if ($this->getOffice() != $this->getAcceptedBy()->getOffice()) {
                $context
                    ->buildViolation('care_request.error.accepting_doctor_office')
                    ->setTranslationDomain('messages')
                    ->atPath('acceptedBy')
                    ->addViolation()
                    ;
            }
        }

        // - le docteur abandonnant
        if ($this->getAbandonedBy()) {
            if ($this->getOffice() != $this->getAbandonedBy()->getOffice()) {
                $context
                    ->buildViolation('care_request.error.abandoned_doctor_office')
                    ->setTranslationDomain('messages')
                    ->atPath('abandonedBy')
                    ->addViolation()
                    ;
            }
        }
    }


    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(['careRequest:read', 'comment:read'])]
    public function getState(): string
    {
        if (empty($this->getId())) {
            return self::STATE_NEW;
        }
        
        if (!empty($this->getAbandonedAt())) {
            return self::STATE_ABANDONED;
        }

        if (!empty($this->getAcceptedAt())) {
            return self::STATE_ARCHIVED;
        }

        return self::STATE_ACTIVE;
    }
    
    public function isNew(): bool
    {
        return $this->getState() === self::STATE_NEW;
    }

    public function isActive(): bool
    {
        return $this->getState() === self::STATE_ACTIVE;
    }

    public function isArchived(): bool
    {
        return $this->getState() === self::STATE_ARCHIVED;
    }

    public function isAbandoned(): bool
    {
        return $this->getState() === self::STATE_ABANDONED;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;

        return $this;
    }

    public function getContactedBy(): ?Doctor
    {
        return $this->contactedBy;
    }

    public function setContactedBy(?Doctor $contactedBy): self
    {
        $this->contactedBy = $contactedBy;

        return $this;
    }
    
    public function getContactedAt(): ?\DateTimeImmutable
    {
        return $this->contactedAt;
    }

    public function setContactedAt(?\DateTimeImmutable $contactedAt): self
    {
        $this->contactedAt = $contactedAt;

        return $this;
    }
    
    public function getContactedAtMutable(): ?\DateTime
    {
        return \DateTime::createFromImmutable($this->getContactedAt());
    }

    public function getPriority(): ?bool
    {
        return $this->priority;
    }

    public function isPriority(): bool
    {
        return $this->getPriority() === true;
    }

    public function setPriority(bool $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getComplaint(): ?Complaint
    {
        return $this->complaint;
    }

    public function setComplaint(?Complaint $complaint): self
    {
        $this->complaint = $complaint;

        return $this;
    }

    public function getCustomComplaint(): ?string
    {
        return $this->customComplaint;
    }

    public function setCustomComplaint(?string $customComplaint): self
    {
        $this->customComplaint = $customComplaint;

        return $this;
    }

    public function getAcceptedBy(): ?Doctor
    {
        return $this->acceptedBy;
    }

    public function setAcceptedBy(?Doctor $acceptedBy): self
    {
        $this->acceptedBy = $acceptedBy;

        return $this;
    }

    public function getAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function setAcceptedAt(?\DateTimeImmutable $acceptedAt): self
    {
        $this->acceptedAt = $acceptedAt;

        return $this;
    }

    public function getAbandonedAt(): ?\DateTimeImmutable
    {
        return $this->abandonedAt;
    }

    public function setAbandonedAt(?\DateTimeImmutable $abandonedAt): self
    {
        $this->abandonedAt = $abandonedAt;

        return $this;
    }

    public function getAbandonedReason(): ?AbandonReason
    {
        return $this->abandonedReason;
    }

    public function setAbandonedReason(?AbandonReason $abandonedReason): self
    {
        $this->abandonedReason = $abandonedReason;

        return $this;
    }

    public function getAbandonedBy(): ?Doctor
    {
        return $this->abandonedBy;
    }

    public function setAbandonedBy(?Doctor $abandonedBy): self
    {
        $this->abandonedBy = $abandonedBy;

        return $this;
    }

    public function getOffice(): ?Office
    {
        if ($this->getPatient() == null) {
            return null;
        }

        return $this->getPatient()->getOffice();
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setCareRequest($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCareRequest() === $this) {
                $comment->setCareRequest(null);
            }
        }

        return $this;
    }
    
    /**
     * @inheritdoc
     */
    public function ownedByOffice(): ?Office
    {
        return $this->getOffice();
    }
    
    public function getActivityObjectName(): string
    {
        return $this->getPatient()->getDisplayName();
    }
    
    public function getActivityIcon(): string
    {
        return 'bi-clipboard';
    }
    
    public function getActivityRoute(): array
    {
        return [
            'name' => 'patient',
            'parameters' => [
                'id' => $this->getPatient()->getId(),
                'careRequest' => $this->getId(),
                '_fragment' => sprintf('care-request-heading-%d', $this->getId()),
            ],
        ];
    }
    
    public function getActivityMessage(string $action): TranslatableMessage
    {
        return new TranslatableMessage(sprintf('activity.care_request.%s', $action), [
            '%careRequestCreationDate%' => $this->getContactedAt()->format('d/m/Y')
        ]);
    }
}

<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CareRequestRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;
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
class CareRequest implements OfficeOwnedInterface
{
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
    private $doctorCreator;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Assert\NotBlank
     */
    #[Groups(['careRequest:read', 'careRequest:put', 'comment:read'])]
    private $creationDate;

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
    private $acceptedByDoctor;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    #[Groups(['careRequest:read', 'careRequest:put'])]
    private $acceptDate;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    #[Groups(['careRequest:read', 'careRequest:put'])]
    private $abandonDate;

    /**
     * @ORM\ManyToOne(targetEntity=AbandonReason::class)
     */
    #[Groups(['careRequest:read', 'careRequest:put'])]
    private $abandonReason;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="careRequest", orphanRemoval=true, cascade={"remove"})
     * @ORM\OrderBy({"creationDate" = "ASC"})
     */
    #[ApiSubresource()]
    private $comments;

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        // Une demande ne peut pas être à la fois abandonnée et archivée (acceptée)
        if ($this->getAcceptDate() != null && $this->getAbandonDate() != null) {
            $context
                ->buildViolation('care_request.error.both_accepted_abandonned')
                ->setTranslationDomain('messages')
                ->atPath('acceptDate')
                ->addViolation()
                ;
        }
        
        // Cohérence office. Le cabinet du patient doit être le même que :
        // - le docteur créateur
        if ($this->getDoctorCreator()) {
            if ($this->getOffice() != $this->getDoctorCreator()->getOffice()) {
                $context
                    ->buildViolation('care_request.error.creating_doctor_office')
                    ->setTranslationDomain('messages')
                    ->atPath('doctorCreator')
                    ->addViolation()
                    ;
            }
        }

        // - le docteur prenant en charge
        if ($this->getAcceptedByDoctor()) {
            if ($this->getOffice() != $this->getAcceptedByDoctor()->getOffice()) {
                $context
                    ->buildViolation('care_request.error.accepting_doctor_office')
                    ->setTranslationDomain('messages')
                    ->atPath('doctorCreator')
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
        
        if (!empty($this->getAbandonDate())) {
            return self::STATE_ABANDONED;
        }

        if (!empty($this->getAcceptDate())) {
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

    public function isAbandonned(): bool
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

    public function getDoctorCreator(): ?Doctor
    {
        return $this->doctorCreator;
    }

    public function setDoctorCreator(?Doctor $doctorCreator): self
    {
        $this->doctorCreator = $doctorCreator;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeImmutable $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }
    
    public function getCreationDateNonImmutable(): ?\DateTime
    {
        return \DateTime::createFromImmutable($this->creationDate);
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

    public function getAcceptedByDoctor(): ?Doctor
    {
        return $this->acceptedByDoctor;
    }

    public function setAcceptedByDoctor(?Doctor $acceptedByDoctor): self
    {
        $this->acceptedByDoctor = $acceptedByDoctor;

        return $this;
    }

    public function getAcceptDate(): ?\DateTimeImmutable
    {
        return $this->acceptDate;
    }

    public function setAcceptDate(?\DateTimeImmutable $acceptDate): self
    {
        $this->acceptDate = $acceptDate;

        return $this;
    }

    public function getAbandonDate(): ?\DateTimeImmutable
    {
        return $this->abandonDate;
    }

    public function setAbandonDate(?\DateTimeImmutable $abandonDate): self
    {
        $this->abandonDate = $abandonDate;

        return $this;
    }

    public function getAbandonReason(): ?AbandonReason
    {
        return $this->abandonReason;
    }

    public function setAbandonReason(?AbandonReason $abandonReason): self
    {
        $this->abandonReason = $abandonReason;

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
}

<?php

namespace App\Entity;

use App\Repository\CareRequestRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CareRequestRepository::class)
 */
#[ApiResource(
    normalizationContext: ['groups' => ['careRequest:read']],
)]
class CareRequest
{
    const STATE_ACTIVE = 'active';
    const STATE_ARCHIVED = 'archived';
    const STATE_ABANDONED = 'abandoned';

    const ABANDONED_NO_ANSWER = 'no_answer';
    const ABANDONED_OTHER_DOCTOR = 'other_doc';
    const ABANDONED_TOO_OLD = '';
    
    // TODO Validation de l'entité. Par exemple ne pas avoir un docteur qui accepte et une date d'abandon

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Patient::class, inversedBy="careRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $patient;

    /**
     * @ORM\ManyToOne(targetEntity=Doctor::class)
     */
    #[Groups(['careRequest:read'])]
    private $doctorCreator;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['careRequest:read'])]
    private $creationDate;

    /**
     * @ORM\Column(type="boolean")
     */
    #[Groups(['careRequest:read'])]
    private $priority;

    /**
     * @ORM\ManyToOne(targetEntity=Complaint::class)
     */
    #[Groups(['careRequest:read'])]
    private $complaint;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups(['careRequest:read'])]
    private $customComplaint;

    /**
     * @ORM\ManyToOne(targetEntity=Doctor::class)
     */
    #[Groups(['careRequest:read'])]
    private $acceptedByDoctor;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    #[Groups(['careRequest:read'])]
    private $acceptDate;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    #[Groups(['careRequest:read'])]
    private $abandonDate;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    #[Groups(['careRequest:read'])]
    private $abandonReason;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getState(): string
    {
        if (!empty($this->getAbandonDate())) {
            return self::STATE_ABANDONED;
        }

        if (!empty($this->getAcceptDate())) {
            return self::STATE_ARCHIVED;
        }

        return self::STATE_ACTIVE;
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

    public function getPriority(): ?bool
    {
        return $this->priority;
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

    public function getAbandonReason(): ?string
    {
        return $this->abandonReason;
    }

    public function setAbandonReason(?string $abandonReason): self
    {
        $this->abandonReason = $abandonReason;

        return $this;
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\PatientPatchAvailabilityController;
use App\Repository\PatientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PatientRepository::class)
 */
#[ApiResource(
    normalizationContext: ['groups' => ['patient:read']],
    itemOperations: [
        'put',
        'delete',
        'get',
        'availability' => [
            'method' => 'PUT',
            'path' => '/patients/{id}/availability',
            'controller' => PatientPatchAvailabilityController::class,
        ]
    ],
)]
class Patient implements OfficeOwnedInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups(['patient:read'])]
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups(['patient:read'])]
    private $lastname;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    #[Groups(['patient:read'])]
    private $birthdate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups(['patient:read'])]
    private $contact;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups(['patient:read'])]
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups(['patient:read'])]
    private $mobilePhone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups(['patient:read'])]
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    #[Groups(['patient:read'])]
    private $variableSchedule;

    /**
     * @ORM\Column(type="json")
     */
    #[Groups(['patient:read'])]
    private $availability = [];

    /**
     * @ORM\ManyToOne(targetEntity=Office::class, inversedBy="patients")
     * @ORM\JoinColumn(nullable=false)
     */
    private $office;

    /**
     * @ORM\OneToMany(targetEntity=CareRequest::class, mappedBy="patient")
     */
    private $careRequests;

    public function __construct()
    {
        $this->careRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeImmutable
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeImmutable $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getMobilePhone(): ?string
    {
        return $this->mobilePhone;
    }

    public function setMobilePhone(?string $mobilePhone): self
    {
        $this->mobilePhone = $mobilePhone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getVariableSchedule(): ?bool
    {
        return $this->variableSchedule;
    }

    public function setVariableSchedule(bool $variableSchedule): self
    {
        $this->variableSchedule = $variableSchedule;

        return $this;
    }

    public function getAvailability(): ?array
    {
        return $this->availability;
    }

    public function setAvailability(array $availability): self
    {
        $this->availability = $availability;

        return $this;
    }

    public function getOffice(): ?Office
    {
        return $this->office;
    }

    public function setOffice(?Office $office): self
    {
        $this->office = $office;

        return $this;
    }

    /**
     * @return Collection|CareRequest[]
     */
    public function getCareRequests(): Collection
    {
        return $this->careRequests;
    }

    public function addCareRequest(CareRequest $careRequest): self
    {
        if (!$this->careRequests->contains($careRequest)) {
            $this->careRequests[] = $careRequest;
            $careRequest->setPatient($this);
        }

        return $this;
    }

    public function removeCareRequest(CareRequest $careRequest): self
    {
        if ($this->careRequests->removeElement($careRequest)) {
            // set the owning side to null (unless already changed)
            if ($careRequest->getPatient() === $this) {
                $careRequest->setPatient(null);
            }
        }

        return $this;
    }
}

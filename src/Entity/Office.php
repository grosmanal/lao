<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\OfficeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=OfficeRepository::class)
 */
#[ApiResource(
    normalizationContext: ['groups' => ['office:read']],
    collectionOperations: [
        'get' => ['security' => "is_granted('ROLE_ADMIN')"],
        'post' => ['security' => "is_granted('ROLE_ADMIN')"],
    ],
    itemOperations: [
        'get' => ['security' => "is_granted('view', object)"],
        'delete' => ['security' => "is_granted('ROLE_ADMIN')"],
        'put' => ['security' => "is_granted('ROLE_ADMIN')"],
    ],
)]
class Office implements OfficeOwnedInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    #[Groups(['office:read'])]
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    #[Groups(['office:read'])]
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    #[Groups(['office:read'])]
    private $addressComplement1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    #[Groups(['office:read'])]
    private $addressComplement2;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    #[Groups(['office:read'])]
    private $zipCode;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    #[Groups(['office:read'])]
    private $city;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    #[Groups(['office:read'])]
    private $country;

    /**
     * @ORM\OneToMany(targetEntity=Doctor::class, mappedBy="office")
     */
    #[Groups(['office:read'])]
    private $doctors;

    /**
     * @ORM\OneToMany(targetEntity=Patient::class, mappedBy="office")
     */
    private $patients;

    public function __construct()
    {
        $this->doctors = new ArrayCollection();
        $this->patients = new ArrayCollection();
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddressComplement1(): ?string
    {
        return $this->addressComplement1;
    }

    public function setAddressComplement1(?string $addressComplement1): self
    {
        $this->addressComplement1 = $addressComplement1;

        return $this;
    }

    public function getAddressComplement2(): ?string
    {
        return $this->addressComplement2;
    }

    public function setAddressComplement2(?string $addressComplement2): self
    {
        $this->addressComplement2 = $addressComplement2;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection|Doctor[]
     */
    public function getDoctors(): Collection
    {
        return $this->doctors;
    }

    /**
     * @codeCoverageIgnore
     */
    public function addDoctor(Doctor $doctor): self
    {
        if (!$this->doctors->contains($doctor)) {
            $this->doctors[] = $doctor;
            $doctor->setOffice($this);
        }

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function removeDoctor(Doctor $doctor): self
    {
        if ($this->doctors->removeElement($doctor)) {
            // set the owning side to null (unless already changed)
            if ($doctor->getOffice() === $this) {
                $doctor->setOffice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Patient[]
     * @codeCoverageIgnore
     */
    public function getPatients(): Collection
    {
        return $this->patients;
    }

    /**
     * @codeCoverageIgnore
     */
    public function addPatient(Patient $patient): self
    {
        if (!$this->patients->contains($patient)) {
            $this->patients[] = $patient;
            $patient->setOffice($this);
        }

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function removePatient(Patient $patient): self
    {
        if ($this->patients->removeElement($patient)) {
            // set the owning side to null (unless already changed)
            if ($patient->getOffice() === $this) {
                $patient->setOffice(null);
            }
        }

        return $this;
    }

    public function ownedByOffice(): ?Office
    {
        return $this;
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DoctorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

// TODO assertions
// TODO test qui peut lire / poster un docteur

/**
 * @ORM\Entity(repositoryClass=DoctorRepository::class)
 */
#[ApiResource()]
class Doctor
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['careRequest:read'])]
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['careRequest:read'])]
    private $lastname;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="doctor", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Office::class, inversedBy="doctors")
     * @ORM\JoinColumn(nullable=false)
     */
    private $office;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

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

    public function __toString()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }
}

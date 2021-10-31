<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DoctorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DoctorRepository::class)
 */
#[ApiResource(
    security: "is_granted('ROLE_ADMIN')"
    // TODO dans le futur les docteurs pourront créer d'autres docteurs du même cabinet
)]
class Doctor extends User
{
    /**
     * @ORM\ManyToOne(targetEntity=Office::class, inversedBy="doctors")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\NotBlank
     */
    private $office;

    public function getOffice(): ?Office
    {
        return $this->office;
    }

    public function setOffice(?Office $office): self
    {
        $this->office = $office;

        return $this;
    }
}
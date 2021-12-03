<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\DoctorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DoctorRepository::class)
 */
#[ApiResource(
    security: "is_granted('ROLE_ADMIN')"
)]
class Doctor extends User implements DoctorOwnedInterface
{
    /**
     * @ORM\ManyToOne(targetEntity=Office::class, inversedBy="doctors")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\NotBlank
     */
    private $office;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="doctor", orphanRemoval=true)
     */
    #[ApiSubresource()]
    private $notifications;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
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
            $notification->setDoctor($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getDoctor() === $this) {
                $notification->setDoctor(null);
            }
        }

        return $this;
    }
    
    public function ownedByDoctor(): ?Doctor
    {
        return $this;
    }
}
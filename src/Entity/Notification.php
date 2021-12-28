<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
#[ApiResource(
    collectionOperations: [
        'get',
        'post' => ['security' => "is_granted('ROLE_ADMIN')"],
    ],
    itemOperations: [
        'get' => ['security' => "is_granted('view', object)"],
        'put' => [
            
            'security' => "is_granted('edit', object)",
            'denormalization_context' => ['groups' => ['notification:put']],
        ],
        'delete' => ['security' => "is_granted('ROLE_ADMIN')"],
    ],
)]
class Notification
{
    const STATE_NEW = 'new';
    const STATE_VIEWED = 'viewed';
    const STATE_ARCHIVED = 'archived';
    
    const STATES = [
        self::STATE_NEW,
        self::STATE_VIEWED,
        self::STATE_ARCHIVED,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Comment::class, inversedBy="notifications")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity=Doctor::class, inversedBy="notifications")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    private $doctor;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Assert\NotBlank
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    #[Groups(['notification:put'])]
    private $readAt;

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        // Cohérence office. Le comment et le doctor doivent être du même office
        if ($this->getComment() && $this->getDoctor()) {
            if ($this->getComment()->getOffice() != $this->getDoctor()->getOffice()) {
                $context
                    ->buildViolation('Comment office mismatch doctor’s one')
                    ->atPath('comment')
                    ->addViolation()
                    ;
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getDoctor(): ?Doctor
    {
        return $this->doctor;
    }

    public function setDoctor(?User $doctor): self
    {
        $this->doctor = $doctor;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable // TODO
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeImmutable $readAt): self
    {
        $this->readAt = $readAt;

        return $this;
    }
}

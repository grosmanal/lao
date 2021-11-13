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
     * @ORM\Column(type="string", length=255)
     * @Assert\Choice(choices=Notification::STATES))
     * @Assert\NotBlank
     */
    #[Groups(['notification:put'])]
    private $state;

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

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }
}

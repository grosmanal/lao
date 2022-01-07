<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="profil", type="string")
 * @ORM\DiscriminatorMap({"user" = "User", "doctor" = "Doctor"})
 * @ORM\Table(name="`user`")
 * @UniqueEntity("email")
 * @Vich\Uploadable
 */
#[ApiResource(
    security: "is_granted('ROLE_ADMIN')"
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, \Serializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['mentionsData'])]
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=180, unique=true)
     */
    #[Assert\NotBlank()]
    #[Assert\Email()]
    #[Groups(['office:read'])]
    private $email;

    /**
     * @var array
     * @ORM\Column(type="json")
     */
    #[Assert\NotBlank()]
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    #[Assert\NotBlank()]
    private $password;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    #[Assert\NotBlank()]
    #[Assert\Length(max: 255)]
    #[Groups(['careRequest:read', 'comment:read', 'office:read'])]
    private $firstname;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    #[Assert\NotBlank()]
    #[Assert\Length(max: 255)]
    #[Groups(['careRequest:read', 'comment:read', 'office:read'])]
    private $lastname;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $updatedAt;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Assert\Length(max: 255)]
    private $avatarName;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="user_avatar", fileNameProperty="avatarName")
     */
    #[Assert\File(
        maxSize: '512k',
        mimeTypes: ['image/jpeg', 'image/png'],
    )]
    private $avatarFile;

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    /**
     * Get the value of updateAt
     *
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Set the value of updateAt
     *
     * @param \DateTimeImmutable|null $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get the value of avatarName
     */
    public function getAvatarName(): ?string
    {
        return $this->avatarName;
    }

    /**
     * Set the value of avatarName
     *
     * @return self
     */
    public function setAvatarName(?string $avatarName): self
    {
        $this->avatarName = $avatarName;

        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $avatarFile
     * @return self
     */
    public function setAvatarFile(?File $avatarFile = null): self
    {
        $this->avatarFile = $avatarFile;

        if (null !== $avatarFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }



    #[Groups(['mentionsData'])]
    public function getDisplayName(): ?string
    {
        return $this->getFirstname();
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize([

            $this->getId(),
            $this->getUserIdentifier(),
            $this->getPassword(),
        ]);
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        $unserializedData = unserialize($serialized);
        $this->id = $unserializedData[0];
        $this->setEmail($unserializedData[1]);
        $this->setPassword($unserializedData[2]);
    }

    public function __toString()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }
}

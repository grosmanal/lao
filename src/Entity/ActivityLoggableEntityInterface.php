<?php

namespace App\Entity;

use Symfony\Component\Translation\TranslatableMessage;

interface ActivityLoggableEntityInterface
{
    public function getCreationDate(): ?\DateTimeImmutable;
    public function getModificationDate(): ?\DateTimeImmutable;
    public function setCreationDate(\DateTimeImmutable $modificationDate): self;
    public function setModificationDate(?\DateTimeImmutable $modificationDate): self;
    public function getCreator(): ?User;
    public function setCreator(?User $user): self;
    public function getModifier(): ?User;
    public function setModifier(?User $user): self;
    public function getActivityIcon(): string;
    public function getActivityMessage(string $action): TranslatableMessage;
    public function getActivityRoute(): array;
}
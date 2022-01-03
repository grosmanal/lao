<?php

namespace App\Entity;

use Symfony\Component\Translation\TranslatableMessage;

interface ActivityLoggableEntityInterface
{
    public function getCreatedBy(): ?User;
    public function getCreatedAt(): ?\DateTimeImmutable;
    public function getModifiedBy(): ?User;
    public function getModifiedAt(): ?\DateTimeImmutable;
    public function setCreatedBy(?User $user): self;
    public function setCreatedAt(\DateTimeImmutable $createdAt): self;
    public function setModifiedBy(?User $user): self;
    public function setModifiedAt(?\DateTimeImmutable $modifiedAt): self;
    public function getActivityObjectName(): string;
    public function getActivityIcon(): string;
    public function getActivityMessage(string $action): TranslatableMessage;
    public function getActivityRoute(): array;
}

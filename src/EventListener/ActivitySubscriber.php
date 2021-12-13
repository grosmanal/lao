<?php

namespace App\EventListener;

use App\Entity\ActivityLoggableEntityInterface;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

class ActivitySubscriber implements EventSubscriberInterface
{
    public function __construct(private Security $security)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }
    
    /**
     * Alimentation de la date de création
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        /** @var ActivityLoggableEntityInterface */
        $entity = $args->getObject();
        if (!$entity instanceof ActivityLoggableEntityInterface) {
            return;
        }

        if (empty($entity->getCreator())) {
            $entity->setCreator($this->security->getUser());
        }

        if (empty($entity->getCreationDate())) {
            $entity->setCreationDate(new \DateTimeImmutable());
        }
    }
    
    /**
     * Alimentation de la date de modification
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        /** @var ActivityLoggableEntityInterface */
        $entity = $args->getObject();
        if (!$entity instanceof ActivityLoggableEntityInterface) {
            return;
        }

        $entity->setModifier($this->security->getUser());
        $entity->setModificationDate(new \DateTimeImmutable());
    }
}
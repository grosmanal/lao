<?php

namespace App\EventListener;

use App\Entity\Comment;
use App\Service\Notification;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class NotificationCreator
{
    public function __construct(private Notification $notification)
    {
    }

    public function prePersist(Comment $comment, LifecycleEventArgs $lifecycleEventArgs)
    {
        foreach ($this->notification->generateNotificationsForComment($comment) as $notification) {
            $comment->addNotification($notification);
        }
    }
}
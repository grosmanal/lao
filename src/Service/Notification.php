<?php

namespace App\Service;

use App\Entity\Office;
use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Notification as NotificationEntity;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class Notification  
{
    public function __construct(
        private UserRepository $userRepository,
        private NormalizerInterface $normalizer,
        private NotificationRepository $notificationRepository,
    ) {
    }
    

    public function hintMentionData(Office $office)
    {
        return $this->normalizer->normalize($office->getDoctors(), null, [
                'groups' => ['mentionsData'],
        ]);
    }

    // TODO transformer en private puis inclure le test dans celui de createNotificationForComment
    private function getUsersMentioned($html)
    {
        $crawler = new Crawler($html);
        
        $usersId = $crawler->filter('span.mention')->each(function(Crawler $node, $i) {
            return $node->attr('data-mention-doctor-id');
        });
        
        $usersId = array_unique($usersId);
        
        $users = array_map(function($userId) {
            return $this->userRepository->find($userId);
        }, $usersId);
        
        // Ce filter sert lors du chargement des fixtures : les users ne sont 
        // pas encore en bdd (il n'y a pas eu de flush)
        // TODO voir s'il ne faut pas charger les fixtures en deux fois
        return array_filter($users, function($user) { return $user !== null; });
    }
    
    /**
     * Retourne faux si une notification existe déjà pour ce user
     * @param Comment
     * @param User
     * @return bool 
     */
    private function notificationAlreadExists(Comment $comment, User $user): bool
    {
        foreach ($comment->getNotifications() as $notification) {
            if ($notification->getUser() === $user) {
                return true;
            }
        }
        return false;
    }

    /**
     * Génération d'entités Notification pour chaque mention du commentaire
     */
    public function generateNotificationsForComment(Comment $comment): array
    {
        // Recherche de la liste des utilisateurs à notifier
        $users = $this->getUsersMentioned($comment->getContent());

        if (empty($users)) {
            // Aucune mention dans le commentaire
            return [];
        }
        
        $notifications = [];
        foreach ($users as $user) {
            // Recherche d'une notification existante
            if (!$this->notificationAlreadExists($comment, $user)) {
                $notification = new NotificationEntity();
                $notification
                    ->setUser($user)
                    ->setComment($comment)
                    ->setState(NotificationEntity::STATE_NEW)
                    ;
                    
                $notifications[] = $notification;
            }
        }
        
        return $notifications;
    }
}

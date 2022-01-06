<?php

namespace App\Service;

use App\Entity\Office;
use App\Entity\Comment;
use App\Entity\Doctor;
use App\Entity\Notification as NotificationEntity;
use App\Exception\DifferentOfficeException;
use App\Repository\NotificationRepository;
use App\Repository\DoctorRepository;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class Notification
{
    public const ALL_MENTION = 'tou·te·s';
    public const ALL_ID = 0;

    public function __construct(
        private DoctorRepository $doctorRepository,
        private NormalizerInterface $normalizer,
        private NotificationRepository $notificationRepository,
    ) {
    }


    public function hintMentionData(Office $office)
    {
        return array_merge(
            [ [ 'id' => self::ALL_ID, 'displayName' => self::ALL_MENTION ], ],
            $this->normalizer->normalize($office->getDoctors(), null, [
                    'groups' => ['mentionsData'],
            ])
        );
    }

    private function getDoctorsMentioned(Comment $comment)
    {
        $crawler = new Crawler($comment->getContent());

        $doctorsId = $crawler->filter('span.mention')->each(function (Crawler $node, $i) {
            return $node->attr('data-mention-doctor-id');
        });

        if (in_array(self::ALL_ID, $doctorsId)) {
            // On a mentionné «tou·te·s» il faut retourner l'ensemble des docteurs du cabinet
            // Sauf l'auteur du commentaire
            $commentAuthor = $comment->getAuthor();
            $doctors = array_filter(
                iterator_to_array($comment->getOffice()->getDoctors()),
                function (Doctor $doctor) use ($commentAuthor) {
                    return $doctor->getId() != $commentAuthor->getId();
                }
            );
        } else {
            $doctorsId = array_unique($doctorsId);

            $doctors = array_map(function ($doctorId) {
                return $this->doctorRepository->find($doctorId);
            }, $doctorsId);
        }


        // Ce filter sert lors du chargement des fixtures : les doctors ne sont
        // pas encore en bdd (il n'y a pas eu de flush)
        return array_filter($doctors, function ($doctor) {
            return $doctor !== null;
        });
    }

    /**
     * Retourne faux si une notification existe déjà pour ce doctor
     * @param Comment
     * @param Doctor
     * @return bool
     */
    private function notificationAlreadExists(Comment $comment, Doctor $doctor): bool
    {
        foreach ($comment->getNotifications() as $notification) {
            if ($notification->getDoctor() === $doctor) {
                return true;
            }
        }
        return false;
    }

    /**
     * Génération d'entités Notification pour chaque mention du commentaire
     * @return NotificationEntity[]
     */
    public function generateNotificationsForComment(Comment $comment): array
    {
        // Recherche de la liste des utilisateurs à notifier
        $doctors = $this->getDoctorsMentioned($comment);

        if (empty($doctors)) {
            // Aucune mention dans le commentaire
            return [];
        }

        $notifications = [];
        foreach ($doctors as $doctor) {
            // Vérification que le doctor correspond au même Office que le commentaire
            if ($doctor->getOffice() != $comment->getOffice()) {
                throw new DifferentOfficeException(
                    'care request ' . $comment->getCareRequest()->getId(),
                    'doctor ' . $doctor->getId()
                );
            }

            // Recherche d'une notification existante
            if (!$this->notificationAlreadExists($comment, $doctor)) {
                $notification = new NotificationEntity();
                $notification
                    ->setDoctor($doctor)
                    ->setComment($comment)
                    ->setCreatedAt(new \DateTimeImmutable('now'))
                    ;

                $notifications[] = $notification;
            }
        }

        return $notifications;
    }
}

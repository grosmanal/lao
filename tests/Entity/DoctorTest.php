<?php

namespace App\Tests\Entity;

use App\Entity\Doctor;
use App\Repository\DoctorRepository;

/**
 * Tests faits pour atteindre la couverture de code maximale
 */
class DoctorTest extends AbstractEntityTestCase
{
    /** @var DoctorRepository */
    private $repository;
    
    public function setUp(): void
    {
        $this->setUpTestEntity([
            __DIR__ . '/../../fixtures/tests/doctor.yaml',
            __DIR__ . '/../../fixtures/tests/notification.yaml',
        ]);
        
        $this->repository = $this->em->getRepository(Doctor::class);
    }
    
    public function testFind()
    {
        $doctor = $this->repository->find(1);

        $notifications = $doctor->getNotifications();
        $notificationsCount = count($notifications);
        $firstNotification = $notifications[0];

        // Suppression de la premiere notification
        $doctor->removeNotification($firstNotification);
        $this->em->persist($doctor);

        $doctor = $this->repository->find(1);
        $this->assertCount($notificationsCount - 1, $doctor->getNotifications());

        // Ajout de la notifications précédement supprimée
        $doctor->addNotification($firstNotification);
        $this->em->persist($firstNotification);
        $this->em->persist($doctor);
        $this->em->flush();

        $doctor = $this->repository->find(1);
        $this->assertCount($notificationsCount, $doctor->getNotifications());
    }
}
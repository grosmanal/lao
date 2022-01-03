<?php

namespace App\Tests\EventListener;

use App\Entity\Doctor;
use App\Entity\Patient;
use App\Entity\CareRequest;
use App\Entity\Comment;
use App\Tests\Controller\AbstractControllerTestCase;
use Doctrine\ORM\EntityManagerInterface;

class ActivitySubscriberTest extends AbstractControllerTestCase
{
    private EntityManagerInterface $em;
    private Doctor $currentDoctor;

    protected function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/doctor.yaml',
        ]);

        $container = static::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);

        $doctorRepository = $this->em->getRepository(Doctor::class);
        $this->currentDoctor = $doctorRepository->find(1);
    }

    public function testActivityEntityCreationAndModification()
    {
        $user = $this->getUser('user1@example.com');
        $this->loginUser('user1@example.com');

        // Patient création
        $patient = new Patient();
        $patient
            ->setLastname('test')
            ->setPhone('test')
            ->setCreatedBy($user)
            ->setOffice($this->currentDoctor->getOffice())
        ;
        $this->em->persist($patient);
        $this->em->flush();

        $this->assertNotEmpty($patient->getCreatedBy());
        $this->assertNotEmpty($patient->getCreatedAt());
        $this->assertEmpty($patient->getModifiedAt());

        // Patient modification
        $patient->setFirstname('test');
        $this->em->persist($patient);
        $this->em->flush();
        $this->assertNotEmpty($patient->getModifiedBy());
        $this->assertNotEmpty($patient->getModifiedAt());

        // CareRequest création
        $careRequest = new CareRequest();
        $careRequest
            ->setPatient($patient)
            ->setContactedBy($this->currentDoctor)
            ->setContactedAt(new \DateTimeImmutable())
        ;
        $this->em->persist($careRequest);
        $this->em->flush();
        $this->assertNotEmpty($careRequest->getCreatedBy());
        $this->assertNotEmpty($careRequest->getCreatedAt());
        $this->assertEmpty($careRequest->getModifiedAt());

        // CareRequest modification
        $careRequest->setPriority(true);
        $this->em->persist($careRequest);
        $this->em->flush();
        $this->assertNotEmpty($careRequest->getModifiedBy());
        $this->assertNotEmpty($careRequest->getModifiedAt());

        // Comment création
        $comment = new Comment();
        $comment
            ->setAuthor($this->currentDoctor)
            ->setCareRequest($careRequest)
        ;
        $this->em->persist($comment);
        $this->em->flush();
        $this->assertNotEmpty($comment->getCreatedBy());
        $this->assertNotEmpty($comment->getCreatedAt());
        $this->assertEmpty($comment->getModifiedAt());

        // Comment modification
        $comment->setContent('lorem');
        $this->em->persist($comment);
        $this->em->flush();
        $this->assertNotEmpty($comment->getModifiedBy());
        $this->assertNotEmpty($comment->getModifiedAt());
    }
}

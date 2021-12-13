<?php

namespace App\Tests\EventListener;

use App\Entity\Doctor;
use App\Entity\Patient;
use App\Entity\CareRequest;
use App\Entity\Comment;
use App\Tests\Service\AbstractServiceTest;
use Doctrine\ORM\EntityManagerInterface;

class ActivitySubscriberTest extends AbstractServiceTest
{
    private EntityManagerInterface $em;
    private Doctor $currentDoctor;

    protected function setUp(): void
    {
        $container = static::getContainer();
        
        $this->em = $container->get(EntityManagerInterface::class);

        $this->setUpTestService([
            __DIR__ . '/../../fixtures/tests/doctor.yaml',
        ]
        );

        $doctorRepository = $this->em->getRepository(Doctor::class);
        $this->currentDoctor = $doctorRepository->find(1);
    }

    public function testEmptyCreationDate()
    {
        $user = $this->getUser('user1@example.com');

        // Patient création
        $patient = new Patient();
        $patient
            ->setFirstname('test')
            ->setCreator($user)
            ->setOffice($this->currentDoctor->getOffice())
        ;
        $this->em->persist($patient);
        $this->em->flush();

        $this->assertNotEmpty($patient->getCreationDate());
        $this->assertEmpty($patient->getModificationDate());
        
        // Patient modification
        $patient->setLastname('test');
        $this->em->persist($patient);
        $this->em->flush();
        $this->assertNotEmpty($patient->getModificationDate());
        
        // CareRequest création
        $careRequest = new CareRequest();
        $careRequest
            ->setPatient($patient)
            ->setDoctorCreator($this->currentDoctor)
        ;
        $this->em->persist($careRequest);
        $this->em->flush();
        $this->assertNotEmpty($careRequest->getCreationDate());
        $this->assertEmpty($careRequest->getModificationDate());
        
        // CareRequest modification
        $careRequest->setPriority(true);
        $this->em->persist($careRequest);
        $this->em->flush();
        $this->assertNotEmpty($careRequest->getModificationDate());
        
        // Comment création
        $comment = new Comment();
        $comment
            ->setAuthor($this->currentDoctor)
            ->setCareRequest($careRequest)
        ;    
        $this->em->persist($comment);
        $this->em->flush();
        $this->assertNotEmpty($comment->getCreationDate());
        $this->assertEmpty($comment->getModificationDate());
        
        // Comment modification
        $comment->setContent('lorem');
        $this->em->persist($comment);
        $this->em->flush();
        $this->assertNotEmpty($comment->getModificationDate());
    }
}
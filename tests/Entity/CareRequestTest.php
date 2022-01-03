<?php

namespace App\Tests\Entity;

use App\Entity\CareRequest;
use App\Entity\Doctor;

/**
 * Tests faits pour atteindre la couverture de code maximale
 */
class CareRequestTest extends AbstractEntityTestCase
{
    private $repository;
    private $doctorRepository;

    public function setUp(): void
    {
        $this->setUpTestEntity([
            __DIR__ . '/../../fixtures/tests/care_request.yaml',
            __DIR__ . '/../../fixtures/tests/comment.yaml',
        ]);

        $this->repository = $this->em->getRepository(CareRequest::class);
        $this->doctorRepository = $this->em->getRepository(Doctor::class);
    }

    public function testFind()
    {
        /** @var CareRequest */
        $careRequest = $this->repository->find(1);
        $doctor = $this->doctorRepository->find(3);

        $this->assertFalse($careRequest->isArchived());
        $this->assertFalse($careRequest->isAbandoned());
        $this->assertTrue($careRequest->isPriority());
        $this->assertEquals(new \DateTime('2021-09-27'), $careRequest->getContactedAtMutable());

        $careRequest->setCreatedBy($doctor);
        $this->em->persist($careRequest);
        $this->em->flush();

        /** @var CareRequest */
        $careRequest = $this->repository->find(1);
        $this->assertSame($doctor, $careRequest->getCreatedBy());

        $comments = $careRequest->getComments();
        $commentsCount = count($comments);
        $firstComment = $comments[0];
        // Suppression du premier commentaire
        $careRequest->removeComment($firstComment);
        $this->em->persist($careRequest);
        $this->em->flush();

        /** @var CareRequest */
        $careRequest = $this->repository->find(1);
        $this->assertCount($commentsCount - 1, $careRequest->getComments());

        // Ajout du commentaire
        $careRequest->addComment($firstComment);
        $this->em->persist($firstComment);
        $this->em->persist($careRequest);
        $this->em->flush();

        /** @var CareRequest */
        $careRequest = $this->repository->find(1);
        $this->assertCount($commentsCount, $careRequest->getComments());
    }
}

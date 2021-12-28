<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Doctor;
use App\Entity\CareRequest;

/**
 * Tests faits pour atteindre la couverture de code maximale
 */
class CommentTest extends AbstractEntityTestCase
{
    public function setUp(): void
    {
        $this->setUpTestEntity([
            __DIR__ . '/../../fixtures/tests/comment.yaml',
            __DIR__ . '/../../fixtures/tests/care_request.yaml',
        ]);
    }
    
    public function testSetCreator()
    {
        $doctor = $this->em->getRepository(Doctor::class)->find(1);
        $comment = (new Comment())
            ->setCreatedBy($doctor)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setCareRequest($this->em->getRepository(CareRequest::class)->find(1))
            ->setContent('lorem')
        ;
        $this->em->persist($comment);
        $this->em->flush();
        $this->assertSame($doctor, $comment->getAuthor());
    }
}
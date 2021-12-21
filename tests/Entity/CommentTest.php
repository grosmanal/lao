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
    private $repository;
    
    public function setUp(): void
    {
        $this->setUpTestEntity([
            __DIR__ . '/../../fixtures/tests/comment.yaml',
            __DIR__ . '/../../fixtures/tests/care_request.yaml',
        ]);
        
        $this->repository = $this->em->getRepository(Comment::class);
    }
    
    public function testSetCreator()
    {
        $doctor = $this->em->getRepository(Doctor::class)->find(1);
        $comment = (new Comment())
            ->setCreator($doctor)
            ->setCareRequest($this->em->getRepository(CareRequest::class)->find(1))
            ->setContent('lorem')
        ;
        $this->em->persist($comment);
        $this->em->flush();
        $this->assertSame($doctor, $comment->getAuthor());
    }
}
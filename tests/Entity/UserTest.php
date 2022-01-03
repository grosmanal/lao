<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Tests faits pour atteindre la couverture de code maximale
 */
class UserTest extends AbstractEntityTestCase
{
    private $repository;

    public function setUp(): void
    {
        $this->setUpTestEntity([
        ]);

        //$this->repository = $this->em->getRepository(Comment::class);
    }

    public function testSetAvatarFile()
    {
        $file = new File('test.png', false);
        $user = (new User())
            ->setFirstname('test')
            ->setLastname('test')
            ->setEmail('test@test.com')
            ->setPassword('password')
            ->setAvatarFile($file)
        ;

        $this->em->persist($user);
        $this->em->flush();
        $this->assertSame($file, $user->getAvatarFile());
    }
}

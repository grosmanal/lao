<?php

namespace App\Tests\Entity;


use App\Tests\TestCaseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractEntityTestCase extends WebTestCase
{
    use TestCaseTrait;

    protected EntityManagerInterface $em;

    protected function setUpTestEntity($fixtureFiles = [])
    {
        $this->em = self::getContainer()->get(EntityManagerInterface::class);

        $this->loadFixtures($fixtureFiles);
    }
}
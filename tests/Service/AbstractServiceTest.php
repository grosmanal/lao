<?php

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Tests\TestCaseTrait;

abstract class AbstractServiceTest extends KernelTestCase
{
    use TestCaseTrait;

    protected function setUpTestService($fixtureFiles = []): void
    {
        $this->loadFixtures($fixtureFiles);
    }
}

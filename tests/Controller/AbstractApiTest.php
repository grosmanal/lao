<?php
namespace App\Tests\Controller;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

abstract class AbstractApiTest extends ApiTestCase
{
    use TestTrait;

    protected $client;

    protected function setUpTestController($fixtureFiles = []): void
    {
        $this->client = self::createClient();

        $this->loadFixtures($fixtureFiles);
    }

    protected function loginUser(string $userEmail): void
    {
        // simulation du log de l'utilisateur
        $this->client->getKernelBrowser()->loginUser($this->getUser($userEmail));
    }
}

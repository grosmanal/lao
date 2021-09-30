<?php
namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Tests\TestCaseTrait;

abstract class AbstractApiTestCase extends ApiTestCase
{
    use TestCaseTrait;

    protected Client $client;

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

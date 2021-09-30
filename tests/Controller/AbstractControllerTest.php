<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\TestCaseTrait;

abstract class AbstractControllerTestCase extends WebTestCase
{
    use TestCaseTrait;

    protected KernelBrowser $client;

    protected function setUpTestController($fixtureFiles = []): void
    {
        $this->client = self::createClient();

        // gets the special container that allows fetching private services
        //$container = self::getContainer();

        $this->loadFixtures($fixtureFiles);
    }


    protected function loginUser(string $userEmail): void
    {
        // simulation du log de l'utilisateur
        $this->client->loginUser($this->getUser($userEmail));
    }
}

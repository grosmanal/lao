<?php
namespace App\Tests\Controller;

use App\Repository\UserRepository;

trait TestTrait
{
    protected function loadFixtures(array $fixtureFiles)
    {
        // Injection des données de test
        $loader = self::getContainer()->get('fidry_alice_data_fixtures.loader.doctrine');
        $loader->load(array_merge($fixtureFiles, [
            __DIR__ . '/../../fixtures/tests/user.yaml'
        ]));
    }
    
    protected function getUser(string $userEmail)
    {
        $userRepository = self::getContainer()->get(UserRepository::class);
        return $userRepository->findOneByEmail($userEmail);
    }
}
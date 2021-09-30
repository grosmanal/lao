<?php
namespace App\Tests;

use App\Repository\UserRepository;

trait TestCaseTrait
{
    protected function loadFixtures(array $fixtureFiles)
    {
        // Injection des données de test
        $loader = self::getContainer()->get('fidry_alice_data_fixtures.loader.doctrine');
        $objects = $loader->load(array_merge($fixtureFiles, [
            __DIR__ . '/../fixtures/tests/user.yaml'
        ]));
        
        // très important : https://github.com/theofidry/AliceDataFixtures/issues/84
        $em = self::getContainer()->get('doctrine.orm.default_entity_manager');
        $em->clear();
    }
    
    protected function getUser(string $userEmail)
    {
        $userRepository = self::getContainer()->get(UserRepository::class);
        return $userRepository->findOneByEmail($userEmail);
    }
}
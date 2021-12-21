<?php
namespace App\Tests;

use App\Entity\User;
use App\Entity\Doctor;
use App\Repository\DoctorRepository;
use App\Repository\UserRepository;

trait TestCaseTrait
{
    protected function loadFixtures(array $fixtureFiles)
    {
        // Injection des données de test
        $loader = static::getContainer()->get('fidry_alice_data_fixtures.loader.doctrine');
        $objects = $loader->load(array_merge($fixtureFiles, [
            __DIR__ . '/../fixtures/tests/doctor.yaml',
            __DIR__ . '/../fixtures/tests/user.yaml',
        ]));
        
        // très important : https://github.com/theofidry/AliceDataFixtures/issues/84
        $em = static::getContainer()->get('doctrine.orm.default_entity_manager');
        $em->clear();
    }
    
    protected function getUser(string $userEmail): User
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        return $userRepository->findOneByEmail($userEmail);
    }
    
    protected function getUserAsDoctor(string $userEmail): Doctor
    {
        $doctorRepository = static::getContainer()->get(DoctorRepository::class);
        return $doctorRepository->find($this->getUser($userEmail)->getId());
    }
}
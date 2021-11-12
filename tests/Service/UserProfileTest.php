<?php

namespace App\Tests\Service;

use App\Repository\UserRepository;
use App\Repository\DoctorRepository;
use App\Service\UserProfile;
use Symfony\Component\Security\Core\Security;

class UserProfileTest extends AbstractServiceTest
{
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        
        $this->setUpTestService([]); // load des fixtures user et doctor
    }
    
    protected function setupUserProfile($userEmail): UserProfile
    {
        $container = static::getContainer();
        
        $userRepository = $container->get(UserRepository::class);
        $user = $userRepository->findOneByEmail($userEmail);

        $security = $this->createMock(Security::class);
        $security
            ->method('getUser')
            ->willReturn($user)
            ;
        
        //$container->set(Security::class, $security);
        //$this->userProfile = $container->get(UserProfile::class);

        $userProfile = new UserProfile(
            $security,
            $container->get(DoctorRepository::class)
        );
        
        return $userProfile;
    }
    

    public function dataProviderIsDoctor()
    {
        return [
            ['admin@example.com', false],
            ['user1@example.com', true],
        ];
    }

    /**
     * @dataProvider dataProviderIsDoctor
     */
    public function testIsDoctor($userEmail, $expected): void
    {
        $userProfile = $this->setupUserProfile($userEmail);

        $this->assertSame($userProfile->currentUserIsDoctor(), $expected);
    }
    

    public function dataProviderDoctorId()
    {
        return [
            ['admin@example.com', null],
            ['user1@example.com', 1],
        ];
    }

    /**
     * @dataProvider dataProviderDoctorId
     */
    public function testDoctorId($userEmail, $expected): void
    {
        $userProfile = $this->setupUserProfile($userEmail);

        $this->assertSame($userProfile->currentUserDoctorId(), $expected);
    }
}

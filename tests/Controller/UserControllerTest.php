<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends AbstractControllerTestCase
{
    public function setUp(): void
    {
        $this->setUpTestController([]);
    }


    public function dataProviderUrl()
    {
        return [
            [ '/users' ],
            [ '/users/1' ],
        ];
    }

    /**
     * @dataProvider dataProviderUrl
     */
    public function testAsAnonymous($url)
    {
        $this->client->request('GET', $url);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/login');
    }


    public function dataProviderGetUsersAs()
    {
        return [
            [ 'admin@example.com', Response::HTTP_OK ],
            [ 'user1@example.com', Response::HTTP_FORBIDDEN ],
            [ 'user2@example.com', Response::HTTP_FORBIDDEN ],
        ];
    }

    /**
     * @dataProvider dataProviderGetUsersAs
     */
    public function testGetUsersAs($userEmail, $expected)
    {
        $this->loginUser($userEmail);
        $this->client->request('GET', '/users');
        $this->assertResponseStatusCodeSame($expected);
    }


    public function testGetUsers()
    {
        $this->loginUser('admin@example.com');
        $crawler = $this->client->request('GET', '/users');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.body-content > ul');
        $this->assertCount(
            3,
            $crawler->filter('.body-content > ul > li')
        ); // 3 offices
        $this->assertCount(
            2,
            $crawler->filter('.body-content > ul > li')->first()->children('ul > li')
        ); // 2 doctors in first office
    }


    public function dataProviderGetUserAs()
    {
        return [
            [ 'admin@example.com', Response::HTTP_OK ],
            [ 'user1@example.com', Response::HTTP_OK ],
            [ 'user2@example.com', Response::HTTP_FORBIDDEN ],
        ];
    }

    /**
     * @dataProvider dataProviderGetUserAs
     */
    public function testGetUserAs($userEmail, $expected)
    {
        $this->loginUser($userEmail);
        $this->client->request('GET', '/users/1');
        $this->assertResponseStatusCodeSame($expected);
    }


    public function testGetUser()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', '/users/1');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists("form[name='user']");
        $this->assertInputValueSame('user[firstname]', 'doctor_1_firstname');
        $this->assertInputValueSame('user[lastname]', 'doctor_1_lastname');
        $this->assertInputValueSame('user[email]', 'user1@example.com');
        $this->assertInputValueSame('user[plainPassword][first]', '');
        $this->assertInputValueSame('user[plainPassword][second]', '');

        $this->client->submitForm('Enregistrer', [
            'user[firstname]' => 'doctor_1_firstname_update',
            'user[lastname]' => 'doctor_1_lastname_update',
            'user[email]' => 'user1_update@example.com',
            'user[plainPassword][first]' => 'newPassword123!',
            'user[plainPassword][second]' => 'newPassword123!',
        ]);
        $this->assertResponseIsSuccessful();

        // Recherche du nouveau doctor
        $doctor = $this->getUserAsDoctor('user1_update@example.com');
        $this->assertSame('doctor_1_firstname_update', $doctor->getFirstname());
        $this->assertSame('doctor_1_lastname_update', $doctor->getLastname());
    }
}

<?php

namespace App\Tests\Controller;

class SecurityControllerTest extends AbstractControllerTestCase
{
    public function setUp(): void
    {
        $this->setUpTestController([]);
    }
    

    public function dataProviderLogin()
    {
        return [
            [ 'user1@example.com', '/home' ],
            [ 'admin@example.com', '/users' ],
        ];
    }
    
    /**
     * @dataProvider dataProviderLogin
     */
    public function testLogin($userEmail, $redirectionRoute)
    {
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists("form#login-form");
        $crawler = $this->client->submitForm('Connexion', [
            'email' => $userEmail,
            'password' => 'password',
        ]);
        $this->assertResponseRedirects($redirectionRoute);
    }
    
    public function testWrongPassword()
    {
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $crawler = $this->client->submitForm('Connexion', [
            'email' => 'user1@example.com',
            'password' => 'Not the good password',
        ]);
        $this->assertResponseRedirects('/login');
    }
    

    public function testLoginAlreadyLogged()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseRedirects('/home');
    }
}
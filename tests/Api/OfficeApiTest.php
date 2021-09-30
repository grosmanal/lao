<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class OfficeApiTest extends AbstractApiTestCase
{

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/office.yaml',
        ]);
    }  

    public function dataProviderGetAs()
    {
        return [
            [ 'admin@example.com', Response::HTTP_OK ],
            [ 'user1@example.com', Response::HTTP_FORBIDDEN ],
        ];
    }

    /**
     * @dataProvider dataProviderGetAs
    */
    public function testGetAs($userMail, $expected)
    {
        $this->loginUser($userMail);
        $this->client->request('GET', '/api/offices/1');
        $this->assertResponseStatusCodeSame($expected);

    }
    
    
    // TODO tester les autres méthodes
}

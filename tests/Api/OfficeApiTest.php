<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class OfficeApiTest extends AbstractApiTestCase
{

    const OFFICE_DATA = [
        'name' => 'office',
        'address' => 'address',
        'addressComplement1' => 'addressComplement1',
        'addressComplement2' => 'addressComplement2',
        'zipCode' => 'zipCode',
        'city' => 'city',
        'country' => 'country',
    ];

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
    public function testGetAs($userEmail, $expected)
    {
        $this->loginUser($userEmail);
        $this->client->request('GET', '/api/offices/1');
        $this->assertResponseStatusCodeSame($expected);

    }
    

    public function dataProviderPostAs()
    {
        return [
            [ 'admin@example.com', Response::HTTP_CREATED ],
            [ 'user1@example.com', Response::HTTP_FORBIDDEN ],
        ];
    }
    
    /**
     * @dataProvider dataProviderPostAs
    */
    public function testPostAs($userEmail, $expected)
    {
        $this->loginUser($userEmail);
        $this->client->request('POST', '/api/offices', [
            'json' => self::OFFICE_DATA,
        ]);
        $this->assertResponseStatusCodeSame($expected);
        
        if ($this->client->getResponse()->getStatusCode() == Response::HTTP_CREATED) {
            // Vérification que l'office est bien créé
            $officeApiId = json_decode($this->client->getResponse()->getContent(), true)['@id'];
            $this->client->request('GET', $officeApiId);
            $this->assertResponseIsSuccessful();
        }
    }
    

    public function dataProviderDeleteAs()
    {
        return [
            [ 'admin@example.com', Response::HTTP_NO_CONTENT ],
            [ 'user1@example.com', Response::HTTP_FORBIDDEN ],
        ];
    }
    
    /**
     * @dataProvider dataProviderDeleteAs
     */
    public function testDeleteAs($userEmail, $expected)
    {
        // Création d'une entité pour pouvoir la supprimer
        $this->loginUser('admin@example.com');
        $this->client->request('POST', '/api/offices', [
            'json' => self::OFFICE_DATA,
        ]);
        $this->assertResponseIsSuccessful();

        $officeApiId = json_decode($this->client->getResponse()->getContent(), true)['@id'];
        $this->loginUser($userEmail);
        $this->client->request('DELETE', $officeApiId);
        $this->assertResponseStatusCodeSame($expected);
    }
    

    public function dataProviderPutAs()
    {
        return [
            [ 'admin@example.com', Response::HTTP_OK ],
            [ 'user1@example.com', Response::HTTP_FORBIDDEN ],
        ];
    }
    
    /**
     * @dataProvider dataProviderPutAs
     */
    public function testPutAs($userEmail, $expected)
    {
        $newOfficeName = 'nom modifié';

        $this->loginUser($userEmail);
        $this->client->request('PUT', '/api/offices/1', [
            'json' => [
                'name' => $newOfficeName,
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);

        if ($this->client->getResponse()->getStatusCode() == Response::HTTP_OK) {
            // Vérification que l'office est bien modifiée
            $officeApiId = json_decode($this->client->getResponse()->getContent(), true)['@id'];
            $this->client->request('GET', $officeApiId);
            $this->assertResponseIsSuccessful();
            $this->assertJsonContains([ 'name' => $newOfficeName]);
        }
    }
    

    public function dataProviderPostMissingContent()
    {
        return [
            [ 'name', Response::HTTP_UNPROCESSABLE_ENTITY, ],
            [ 'address', Response::HTTP_UNPROCESSABLE_ENTITY, ],
            [ 'addressComplement1', Response::HTTP_CREATED, ],
            [ 'addressComplement2', Response::HTTP_CREATED, ],
            [ 'zipCode', Response::HTTP_UNPROCESSABLE_ENTITY, ],
            [ 'city', Response::HTTP_UNPROCESSABLE_ENTITY, ],
            [ 'country', Response::HTTP_UNPROCESSABLE_ENTITY, ],
        ];
    }
    
    /** 
     * @dataProvider dataProviderPostMissingContent
     */
    public function testPostMissingContent($content, $expected)
    {
        $this->loginUser('admin@example.com');

        $data = array_diff_key(self::OFFICE_DATA, [$content => null]);
        $this->client->request('POST', "/api/offices", [
            'json' => $data,
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }
}
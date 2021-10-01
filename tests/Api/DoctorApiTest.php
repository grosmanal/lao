<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class DoctorApiTest extends AbstractApiTestCase
{

    const DOCTOR_DATA = [
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'user' => '/api/users/4',
        'office' => '/api/offices/1',
    ];

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/doctor.yaml',
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
        $this->client->request('GET', '/api/doctors/1');
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
        $this->client->request('POST', '/api/doctors', [
            'json' => self::DOCTOR_DATA,
        ]);
        $this->assertResponseStatusCodeSame($expected);
        
        if ($this->client->getResponse()->getStatusCode() == Response::HTTP_CREATED) {
            // Vérification que le docteur est bien créé
            $doctorApiId = json_decode($this->client->getResponse()->getContent(), true)['@id'];
            $this->client->request('GET', $doctorApiId);
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
        $this->client->request('POST', '/api/doctors', [
            'json' => self::DOCTOR_DATA,
        ]);
        $this->assertResponseIsSuccessful();

        $doctorApiId = json_decode($this->client->getResponse()->getContent(), true)['@id'];
        $this->loginUser($userEmail);
        $this->client->request('DELETE', $doctorApiId);
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
        $newDoctorName = 'nom modifié';

        $this->loginUser($userEmail);
        $this->client->request('PUT', '/api/doctors/1', [
            'json' => [
                'firstname' => $newDoctorName,
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);

        if ($this->client->getResponse()->getStatusCode() == Response::HTTP_OK) {
            // Vérification que le docteur est bien modifiée
            $doctorApiId = json_decode($this->client->getResponse()->getContent(), true)['@id'];
            $this->client->request('GET', $doctorApiId);
            $this->assertResponseIsSuccessful();
            $this->assertJsonContains([ 'firstname' => $newDoctorName]);
        }
    }
    

    public function dataProviderPostMissingContent()
    {
        return [
            [ 'firstname', Response::HTTP_UNPROCESSABLE_ENTITY, ],
            [ 'lastname', Response::HTTP_UNPROCESSABLE_ENTITY, ],
            [ 'user', Response::HTTP_UNPROCESSABLE_ENTITY, ],
            [ 'office', Response::HTTP_UNPROCESSABLE_ENTITY, ],
        ];
    }
    
    /** 
     * @dataProvider dataProviderPostMissingContent
     */
    public function testPostMissingContent($content, $expected)
    {
        $this->loginUser('admin@example.com');

        $data = array_diff_key(self::DOCTOR_DATA, [$content => null]);
        $this->client->request('POST', "/api/doctors", [
            'json' => $data,
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }
}

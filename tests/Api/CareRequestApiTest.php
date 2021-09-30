<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class CareRequestApiTest extends AbstractApiTestCase
{
    const CARE_REQUEST_DATA = [
        'patient' => '/api/patients/1',
        'doctorCreator' => '/api/doctors/1',
        'creationDate' => '2021-09-29',
        'priority' => true,
        'complaint' => '/api/complaints/1',
        'customComplaint' => 'custom',
    ];

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/patient.yaml',
            __DIR__ . '/../../fixtures/tests/care_request.yaml',
        ]);
    }  

    public function testGetCareRequest()
    {
        $this->loginUser('admin@example.com');
        $this->client->request('GET', "/api/care_requests/1");
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            'patient' => [
                '@id' => '/api/patients/1',
                '@type' => 'Patient',
                'firstname' => 'patient_1_firstname',
                'lastname' => 'patient_1_lastname',
            ],
            'doctorCreator' => [
                '@id' => '/api/doctors/1',
                '@type' => 'Doctor',
                'firstname' => 'doctor_1_firstname',
                'lastname' => 'doctor_1_lastname',
            ],
            'creationDate' => '2021-09-27T00:00:00+00:00',
            'priority' => true,
            'complaint' => [
                '@id' => '/api/complaints/1',
                '@type' => 'Complaint',
                'label' => 'Plainte 1',
            ],
        ]);
    }

    public function dataProviderGetAsDoctor()
    {
        return [
            ['user1@example.com', Response::HTTP_OK],
            ['user2@example.com', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @dataProvider dataProviderGetAsDoctor 
     */
    public function testGetAsDoctor($userEmail, $expected)
    {
        $this->loginUser($userEmail);
        $this->client->request('GET', "/api/care_requests/1");
        $this->assertResponseStatusCodeSame($expected);
    }


    public function dataProviderGetAllAsDoctor()
    {
        return [
            ['admin@example.com', [
                '/api/care_requests/1',
                '/api/care_requests/2',
                '/api/care_requests/3',
                '/api/care_requests/4',
            ]],
            ['user1@example.com', [
                '/api/care_requests/1',
                '/api/care_requests/2',
                '/api/care_requests/3',
            ]],
            ['user2@example.com', [
                '/api/care_requests/4',
            ]],
        ];
    }

    /**
     * @dataProvider dataProviderGetAllAsDoctor
     */
    public function testGetAllAsDoctor($userEmail, $careRequestApiIds)
    {
        $this->loginUser($userEmail);
        $this->client->getKernelBrowser()->followRedirects();
        $this->client->request('GET', "/api/care_requests/");
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['hydra:totalItems' => count($careRequestApiIds)]);

        // Constitution de la liste des care requests récupérées
        $gotCareRequestsApiIds = [];
        foreach (json_decode($this->client->getResponse()->getContent(), true)['hydra:member'] as $gotCareRequest) {
            $gotCareRequestsApiIds[] = $gotCareRequest['@id'];
        }
        $this->assertSame($careRequestApiIds, $gotCareRequestsApiIds);
    }
    

    public function testPostCareRequest()
    {
        $this->loginUser('admin@example.com');
        $crawler = $this->client->request('POST', "/api/care_requests", [
            'json' => self::CARE_REQUEST_DATA,
        ]);
        $this->assertResponseIsSuccessful();
        $careRequestApiId = json_decode($crawler->getContent(), true)['@id'];
        $this->client->request('GET', $careRequestApiId);
        $this->assertResponseIsSuccessful();
    }


    public function dataProviderMissingContent()
    {
        return [
            ['patient', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['doctorCreator', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['creationDate', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['priority', Response::HTTP_CREATED],
            ['complaint', Response::HTTP_CREATED],
            ['customComplaint', Response::HTTP_CREATED],
        ];
    }

    /**
     * @dataProvider dataProviderMissingContent
     */
    public function testPostMissingContent($content, $expected)
    {
        $this->loginUser('admin@example.com');

        $data = array_diff_key(self::CARE_REQUEST_DATA, [$content => null]);
        $this->client->request('POST', "/api/care_requests", [
            'json' => $data,
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }


    public function dataProviderAnotherOfficeData()
    {
        return [
            ['user1@example.com', 'patient', '/api/patients/1', Response::HTTP_CREATED],
            ['user1@example.com', 'patient', '/api/patients/3', Response::HTTP_FORBIDDEN],
            // ['user_patient_1@example.com', 'patient', '/api/patients/1', ' Response::HTTP_CREATED],
            // ['user_patient_1@example.com', 'patient', '/api/patients/3', ' Response::HTTP_FORBIDDEN],
            ['user1@example.com', 'doctorCreator', '/api/doctors/1', Response::HTTP_CREATED],
            ['user1@example.com', 'doctorCreator', '/api/doctors/2', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @dataProvider dataProviderAnotherOfficeData
     */
    public function testAnotherOfficeData($userEmail, $payloadKey, $payloadValue, $expected)
    {
        $this->loginUser($userEmail);
        $data = array_merge(self::CARE_REQUEST_DATA, [$payloadKey => $payloadValue]);
        $crawler = $this->client->request('POST', "/api/care_requests", [
            'json' => $data,
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }
}

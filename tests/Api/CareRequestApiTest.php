<?php

namespace App\Tests\Api;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

class CareRequestApiTest extends AbstractApiTestCase
{
    private const CARE_REQUEST_DATA = [
        'patient' => '/api/patients/1',
        'contactedBy' => '/api/doctors/1',
        'contactedAt' => '2021-09-29',
        'priority' => true,
        'complaint' => '/api/complaints/1',
        'requestedDoctor' => '/api/doctors/1',
        'customComplaint' => 'custom',
        'acceptedBy' => null,
        'acceptedAt' => null,
    ];

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/patient.yaml',
            __DIR__ . '/../../fixtures/tests/care_request.yaml',
        ]);
    }


    public function testGet()
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
            'contactedBy' => [
                '@id' => '/api/doctors/1',
                '@type' => 'Doctor',
                'firstname' => 'doctor_1_firstname',
                'lastname' => 'doctor_1_lastname',
            ],
            'contactedAt' => '2021-09-27T00:00:00+00:00',
            'requestedDoctor' => [
                '@id' => '/api/doctors/1',
                '@type' => 'Doctor',
                'firstname' => 'doctor_1_firstname',
                'lastname' => 'doctor_1_lastname',
            ],
            'priority' => true,
            'state' => 'active',
            'complaint' => [
                '@id' => '/api/complaints/1',
                '@type' => 'Complaint',
                'label' => 'Plainte 1',
            ],
            'relatedUri' => [
                'getHtmlForm' => '/care_request_forms/1',
            ],
        ]);
    }


    public function dataProviderGetAllAs()
    {
        return [
            ['admin@example.com', [
                '/api/care_requests/1',
                '/api/care_requests/2',
                '/api/care_requests/3',
                '/api/care_requests/4',
                '/api/care_requests/5',
                '/api/care_requests/6',
            ]],
            ['user1@example.com', [
                '/api/care_requests/1',
                '/api/care_requests/2',
                '/api/care_requests/3',
                '/api/care_requests/5',
                '/api/care_requests/6',
            ]],
            ['user2@example.com', [
                '/api/care_requests/4',
            ]],
        ];
    }

    /**
     * @dataProvider dataProviderGetAllAs
     */
    public function testGetAllAs($userEmail, $careRequestApiIds)
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


    public function testPost()
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


    public function dataProviderPostMissingContent()
    {
        return [
            ['patient', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['contactedBy', Response::HTTP_CREATED],
            ['contactedAt', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['requestedDoctor', Response::HTTP_CREATED],
            ['priority', Response::HTTP_CREATED],
            ['complaint', Response::HTTP_CREATED],
            ['customComplaint', Response::HTTP_CREATED],
        ];
    }

    /**
     * @dataProvider dataProviderPostMissingContent
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

            ['user1@example.com', 'contactedBy', '/api/doctors/1', Response::HTTP_CREATED],
            ['user1@example.com', 'contactedBy', '/api/doctors/2', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['admin@example.com', 'contactedBy', '/api/doctors/2', Response::HTTP_UNPROCESSABLE_ENTITY],

            ['user1@example.com', 'requestedDoctor', '/api/doctors/1', Response::HTTP_CREATED],
            ['user1@example.com', 'requestedDoctor', '/api/doctors/2', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['admin@example.com', 'requestedDoctor', '/api/doctors/2', Response::HTTP_UNPROCESSABLE_ENTITY],

            ['user1@example.com', 'acceptedBy', '/api/doctors/1', Response::HTTP_CREATED],
            ['user1@example.com', 'acceptedBy', '/api/doctors/2', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['admin@example.com', 'acceptedBy', '/api/doctors/2', Response::HTTP_UNPROCESSABLE_ENTITY],
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

    public function dataProviderDeleteAs()
    {
        return [
            [ 'admin@example.com', '/api/care_requests/1', Response::HTTP_NO_CONTENT ],
            [ 'user1@example.com', '/api/care_requests/1', Response::HTTP_NO_CONTENT ],
            [ 'user2@example.com', '/api/care_requests/1', Response::HTTP_FORBIDDEN ],
        ];
    }

    /**
     * @dataProvider dataProviderDeleteAs
     */
    public function testDeleteAs($userEmail, $careRequestApiId, $expected)
    {
        $this->loginUser($userEmail);
        $this->client->request('DELETE', $careRequestApiId);
        $this->assertResponseStatusCodeSame($expected);
    }


    public function dataPostInconsistentData()
    {
        return [
            [ null, null, Response::HTTP_CREATED ],
            [ 'now', null, Response::HTTP_CREATED ],
            [ null, 'now', Response::HTTP_CREATED ],
            [ 'now', 'now', Response::HTTP_UNPROCESSABLE_ENTITY ],
        ];
    }

    /**
     * @dataProvider dataPostInconsistentData
     */
    public function testPostInconsistentData($acceptedAt, $abandonedAt, $expected)
    {
        $this->loginUser('admin@example.com');

        // On ne peut pas avoir :
        // - une date accept et une date d'abandon
        $data = array_merge(self::CARE_REQUEST_DATA, [
            'acceptedAt' => $acceptedAt,
            'abandonedAt' => $abandonedAt,
        ]);
        $crawler = $this->client->request('POST', "/api/care_requests", [
            'json' => $data,
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }


    public function dataProviderPutAs()
    {
        return [
            [ 'admin@example.com', '/api/care_requests/1', Response::HTTP_OK ],
            [ 'user1@example.com', '/api/care_requests/1', Response::HTTP_OK ],
            [ 'user2@example.com', '/api/care_requests/1', Response::HTTP_FORBIDDEN ],
        ];
    }

    /**
     * @dataProvider dataProviderPutAs
     */
    public function testPutAs($userEmail, $careRequestApiId, $expected)
    {
        $newCustomComplaint = 'custom complaint modifiée';

        $this->loginUser($userEmail);
        $this->client->request('PUT', $careRequestApiId, [
            'json' => [
                'customComplaint' => $newCustomComplaint,
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);

        if ($this->client->getResponse()->getStatusCode() == Response::HTTP_OK) {
            // Vérification que la care request est bien modifiée
            $careRequestApiId = json_decode($this->client->getResponse()->getContent(), true)['@id'];
            $this->client->request('GET', $careRequestApiId);
            $this->assertResponseIsSuccessful();
            $this->assertJsonContains([ 'customComplaint' => $newCustomComplaint]);
        }
    }

    public function dataProviderPutInconsistentData()
    {
        return [
            ['contactedBy', '/api/doctors/1', Response::HTTP_OK],
            ['contactedBy', '/api/doctors/2', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['requestedDoctor', '/api/doctors/1', Response::HTTP_OK],
            ['requestedDoctor', '/api/doctors/2', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['acceptedBy', '/api/doctors/1', Response::HTTP_OK],
            ['acceptedBy', '/api/doctors/2', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['abandonedBy', '/api/doctors/1', Response::HTTP_OK],
            ['abandonedBy', '/api/doctors/2', Response::HTTP_UNPROCESSABLE_ENTITY],
        ];
    }

    /**
     * @dataProvider dataProviderPutInconsistentData
     */
    public function testPutInconsistentData($payloadKey, $payloadValue, $expected)
    {
        $this->loginUser('admin@example.com');
        $this->client->request('PUT', '/api/care_requests/1', [
            'json' => [
                $payloadKey => $payloadValue,
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }

    public function testPutPatient()
    {
        // On ne peut pas modifier le patient d'une care request
        $this->loginUser('admin@example.com');
        $this->client->request('PUT', '/api/care_requests/1', [
            'json' => [
                'patient' => '/api/patients/2',
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'patient' => [
                '@id' => '/api/patients/1',
            ],
        ]);
    }

    public function dataProviderPutDataTooLong()
    {
        return [
            [ 'customComplaint', 5000, Response::HTTP_OK ],
            [ 'customComplaint', 5001, Response::HTTP_UNPROCESSABLE_ENTITY ],
        ];
    }

    /**
     * @dataProvider dataProviderPutDataTooLong
     */
    public function testPutDataTooLong($payloadKey, $payloadLength, $expected)
    {
        $this->loginUser('admin@example.com');
        $this->client->request('PUT', '/api/care_requests/1', [
            'json' => [
                $payloadKey => str_repeat('A', $payloadLength),
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }
}

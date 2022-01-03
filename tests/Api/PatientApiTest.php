<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class PatientApiTest extends AbstractApiTestCase
{
    private const PATIENT_DATA = [
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'birthdate' => '2011-05-30',
        'contact' => 'contact',
        'phone' => 'phone',
        'mobile_phone' => 'mobile_phone',
        'email' => 'test@example.com',
        'variable_schedule' => true,
        'availability' => [ 1 => [900, 1000] ],
        'office' => '/api/offices/1',
    ];

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/patient.yaml',
        ]);
    }


    public function dataProviderGetAllAsDoctor()
    {
        return [
            ['admin@example.com', [
                '/api/patients/1',
                '/api/patients/2',
                '/api/patients/3',
                '/api/patients/4',
            ]],
            ['user1@example.com', [
                '/api/patients/1',
                '/api/patients/2',
                '/api/patients/4',
            ]],
            ['user2@example.com', [
                '/api/patients/3',
            ]],
        ];
    }

    /**
     * @dataProvider dataProviderGetAllAsDoctor
     */
    public function testGetAllAsDoctor($userEmail, $patientsApiIds)
    {
        $this->loginUser($userEmail);
        $this->client->getKernelBrowser()->followRedirects();
        $this->client->request('GET', "/api/patients/");
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['hydra:totalItems' => count($patientsApiIds)]);

        // Constitution de la liste des care requests récupérées
        $gotPatientsApiIds = [];
        foreach (json_decode($this->client->getResponse()->getContent(), true)['hydra:member'] as $gotPatient) {
            $gotPatientsApiIds[] = $gotPatient['@id'];
        }
        $this->assertSame($patientsApiIds, $gotPatientsApiIds);
    }


    public function testGetPatient()
    {
        $this->loginUser('admin@example.com');
        $this->client->request('GET', "/api/patients/1");
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@id' => '/api/patients/1',
            '@type' => 'Patient',
            'firstname' => 'patient_1_firstname',
            'lastname' => 'patient_1_lastname',
        ]);
    }


    public function dataProviderGetAsDoctor()
    {
        return [
            [ 'admin@example.com', '/api/patients/1', Response::HTTP_OK ],
            [ 'admin@example.com', '/api/patients/2', Response::HTTP_OK ],
            [ 'admin@example.com', '/api/patients/3', Response::HTTP_OK ],
            [ 'user1@example.com', '/api/patients/1', Response::HTTP_OK ],
            [ 'user1@example.com', '/api/patients/3', Response::HTTP_FORBIDDEN ],
            [ 'user2@example.com', '/api/patients/1', Response::HTTP_FORBIDDEN ],
            [ 'user2@example.com', '/api/patients/3', Response::HTTP_OK ],
        ];
    }

    /**
     * @dataProvider dataProviderGetAsDoctor
     */
    public function testGetAsDoctor($userEmail, $patientsApiId, $expected)
    {
        $this->loginUser($userEmail);
        $this->client->request('GET', $patientsApiId);
        $this->assertResponseStatusCodeSame($expected);
    }


    public function testPostPatient()
    {
        $this->loginUser('admin@example.com');
        $crawler = $this->client->request('POST', "/api/patients", [
            'json' => self::PATIENT_DATA,
        ]);
        $this->assertResponseIsSuccessful();
        $patientApiId = json_decode($crawler->getContent(), true)['@id'];
        $this->client->request('GET', $patientApiId);
        $this->assertResponseIsSuccessful();
    }


    public function dataProviderPostMissingContent()
    {
        return [
            ['firstname', Response::HTTP_CREATED],
            ['lastname', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['birthdate', Response::HTTP_CREATED],
            ['contact', Response::HTTP_CREATED],
            ['phone', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['mobile_phone', Response::HTTP_CREATED],
            ['email', Response::HTTP_CREATED],
            ['variable_schedule', Response::HTTP_CREATED],
            ['availability', Response::HTTP_CREATED],
            ['office', Response::HTTP_UNPROCESSABLE_ENTITY],
        ];
    }

    /**
     * @dataProvider dataProviderPostMissingContent
     */
    public function testPostMissingContent($content, $expected)
    {
        $this->loginUser('admin@example.com');

        $data = array_diff_key(self::PATIENT_DATA, [$content => null]);
        $this->client->request('POST', "/api/patients", [
            'json' => $data,
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }


    public function dataProviderDeleteAs()
    {
        return [
            [ 'admin@example.com', '/api/patients/1', Response::HTTP_NO_CONTENT ],
            [ 'user1@example.com', '/api/patients/1', Response::HTTP_NO_CONTENT ],
            [ 'user2@example.com', '/api/patients/1', Response::HTTP_FORBIDDEN ],
        ];
    }

    /**
     * @dataProvider dataProviderDeleteAs
     */
    public function testDeleteAs($userEmail, $patientApiId, $expected)
    {
        $this->loginUser($userEmail);
        $this->client->request('DELETE', $patientApiId);
        $this->assertResponseStatusCodeSame($expected);
    }


    public function dataProviderPutAs()
    {
        return [
            [ 'admin@example.com', '/api/patients/1', Response::HTTP_OK ],
            [ 'user1@example.com', '/api/patients/1', Response::HTTP_OK ],
            [ 'user2@example.com', '/api/patients/1', Response::HTTP_FORBIDDEN ],
        ];
    }

    /**
     * @dataProvider dataProviderPutAs
     */
    public function testPutAs($userEmail, $patientApiId, $expected)
    {
        $newPatientFirstname = 'firstname modifié';

        $this->loginUser($userEmail);
        $this->client->request('PUT', $patientApiId, [
            'json' => [
                'firstname' => $newPatientFirstname,
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);

        if ($this->client->getResponse()->getStatusCode() == Response::HTTP_OK) {
            // Vérification que le patient est bien modifié
            $patientApiId = json_decode($this->client->getResponse()->getContent(), true)['@id'];
            $this->client->request('GET', $patientApiId);
            $this->assertResponseIsSuccessful();
            $this->assertJsonContains([ 'firstname' => $newPatientFirstname]);
        }
    }


    public function dataProviderPutInconsistentData()
    {
        return [
            [ 'email', 'test@example.com', Response::HTTP_OK ],
            [ 'email', 'not_an_email', Response::HTTP_UNPROCESSABLE_ENTITY ],
        ];
    }

    /**
     * @dataProvider dataProviderPutInconsistentData
     */
    public function testPutInconsistentData($payloadKey, $payloadValue, $expected)
    {
        $this->loginUser('admin@example.com');
        $this->client->request('PUT', '/api/patients/1', [
            'json' => [
                $payloadKey => $payloadValue,
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }


    public function dataProviderPutDataTooLong()
    {
        return [
            [ 'firstname', 255, Response::HTTP_OK ],
            [ 'firstname', 256, Response::HTTP_UNPROCESSABLE_ENTITY ],
            [ 'lastname', 255, Response::HTTP_OK ],
            [ 'lastname', 256, Response::HTTP_UNPROCESSABLE_ENTITY ],
            [ 'contact', 255, Response::HTTP_OK ],
            [ 'contact', 256, Response::HTTP_UNPROCESSABLE_ENTITY ],
            [ 'phone', 255, Response::HTTP_OK ],
            [ 'phone', 256, Response::HTTP_UNPROCESSABLE_ENTITY ],
            [ 'email', 255, Response::HTTP_OK ],
            [ 'email', 256, Response::HTTP_UNPROCESSABLE_ENTITY ],
        ];
    }

    /**
     * @dataProvider dataProviderPutDataTooLong
     */
    public function testPutDataTooLong($payloadKey, $payloadLength, $expected)
    {
        $this->loginUser('admin@example.com');

        $payloadValue = str_repeat('A', $payloadLength);
        if ($payloadKey == 'email') {
            // Remplacement de la fin du la chaîne par '@example.com'
            $payloadValue = substr_replace($payloadValue, '@example.com', -12);
        }

        $this->client->request('PUT', '/api/patients/1', [
            'json' => [
                $payloadKey => $payloadValue,
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }
}

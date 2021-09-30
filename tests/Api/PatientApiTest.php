<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class PatientApiTest extends AbstractApiTestCase
{
    const PATIENT_DATA = [
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'birthdate' => 'birthdate',
        'contact' => 'contact',
        'phone' => 'phone',
        'mobile_phone' => 'mobile_phone',
        'email' => 'email',
        'variable_schedule' => true,
        'availability' => [ 1 => [900, 1000] ],
    ];

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/doctor.yaml',
            __DIR__ . '/../../fixtures/tests/patient.yaml',
        ]);
    }  

    // TODO plein de tests à faire

    
    public function dataProviderGetAllAsDoctor()
    {
        return [
            ['admin@example.com', [
                '/api/patients/1',
                '/api/patients/2',
                '/api/patients/3',
            ]],
            ['user1@example.com', [
                '/api/patients/1',
                '/api/patients/2',
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
    

}
<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class PatientPatchAvailabilityTest extends AbstractApiTestCase
{
    const AVAILABILITY_PATCH = [
        'weekDay' => 1,
        'start' => '0900',
        'end' => '0930',
        'available' => true,
    ];

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/doctor.yaml',
            __DIR__ . '/../../fixtures/tests/patient.yaml',
        ]);
    }    

    public function testPatchAsAnonymous(): void
    {
        $crawler = $this->client->request('PUT', "/api/patients/1/availability", ['json' => self::AVAILABILITY_PATCH]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND); // Redirection vers le login
        $this->assertResponseRedirects('/login');
    }


    public function dataProviderTestPatch()
    {
        return [
            [1, Response::HTTP_OK],
            [99, Response::HTTP_NOT_FOUND],
        ];
    }
    /**
     * @dataProvider dataProviderTestPatch
     */
    public function testPatch($patientId, $expected): void
    {
        $this->loginUser('admin@example.com');
        $crawler = $this->client->request('PUT', "/api/patients/$patientId/availability", ['json' => self::AVAILABILITY_PATCH]);
        $this->assertResponseStatusCodeSame($expected);
    }


    public function dataProviderTestPatchAsDoctor()
    {
        return [
            [ 'user1@example.com', Response::HTTP_OK ],
            [ 'user2@example.com', Response::HTTP_FORBIDDEN ],
        ];
    }

    /**
     * @dataProvider dataProviderTestPatchAsDoctor
     */
    public function testPatchAsDoctor($doctorEmail, $expected)
    {
        $this->loginUser($doctorEmail);
        $crawler = $this->client->request('PUT', "/api/patients/1/availability", ['json' => self::AVAILABILITY_PATCH]);
        $this->assertResponseStatusCodeSame($expected);
    }

    public function testInconsistentData()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('PUT', "/api/patients/1/availability", ['json' => ['foo' => 'bar']]);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function dataProviderTestMissingContent()
    {
        return [
            [ 'weekDay' ],
            [ 'available' ],
            [ 'start' ],
            [ 'end' ],
        ];
    }
    
    /**
     * @dataProvider dataProviderTestMissingContent
     */
    public function testMissingContent($content)
    {
        $this->loginUser('user1@example.com');

        $data = array_diff_key(self::AVAILABILITY_PATCH, [$content => null]);
        $crawler = $this->client->request('PUT', "/api/patients/1/availability", ['json' => $data]);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}

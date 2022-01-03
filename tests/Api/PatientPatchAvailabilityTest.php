<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class PatientPatchAvailabilityTest extends AbstractApiTestCase
{
    private const AVAILABILITY_PATCH_ONE_DAY = [
        'weekDays' => [ 1 ],
        'start' => '0900',
        'end' => '0930',
        'available' => true,
    ];

    private const AVAILABILITY_PATCH_MULTIPLE_DAYS = [
        'weekDays' => [1, 2, 3],
        'start' => '0900',
        'end' => '0930',
        'available' => true,
    ];

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/patient.yaml',
        ]);
    }

    public function testPatchAsAnonymous(): void
    {
        $crawler = $this->client->request(
            'PUT',
            "/api/patients/1/availability",
            ['json' => self::AVAILABILITY_PATCH_ONE_DAY]
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND); // Redirection vers le login
        $this->assertResponseRedirects('/login');
    }


    public function dataProviderTestPatchPatient()
    {
        return [
            [1, Response::HTTP_OK],
            [99, Response::HTTP_NOT_FOUND],
        ];
    }
    /**
     * @dataProvider dataProviderTestPatchPatient
     */
    public function testPatchPatient($patientId, $expected): void
    {
        $this->loginUser('admin@example.com');
        $crawler = $this->client->request(
            'PUT',
            "/api/patients/$patientId/availability",
            ['json' => self::AVAILABILITY_PATCH_ONE_DAY]
        );
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
        $crawler = $this->client->request(
            'PUT',
            "/api/patients/1/availability",
            ['json' => self::AVAILABILITY_PATCH_ONE_DAY]
        );
        $this->assertResponseStatusCodeSame($expected);
    }

    public function dataProviderTestPatch()
    {
        return [
            [ self::AVAILABILITY_PATCH_ONE_DAY, [
                '1' => [
                    [900, 930],
                    [1000, 1200],
                ],
            ] ],
            [ self::AVAILABILITY_PATCH_MULTIPLE_DAYS, [
                '1' => [
                    [900, 930],
                    [1000, 1200],
                ],
                '2' => [
                    [900, 930],
                ],
            ] ],
        ];
    }

    /**
     * @dataProvider dataProviderTestPatch
     */
    public function testPatch($data, $expected)
    {
        $this->loginUser('admin@example.com');
        $crawler = $this->client->request('PUT', "/api/patients/1/availability", ['json' => $data]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Lecture de la disponibilité du patient pour vérification de la suppression
        $crawler = $this->client->request('GET', "/api/patients/1");
        $this->assertJsonContains([
            'availability' => $expected,
        ]);
    }


    public function dataProviderInconsistentWeekDays()
    {
        return [
            [ 'chaineDeCaractères', Response::HTTP_BAD_REQUEST ],
            [ [0, 1, 2], Response::HTTP_UNPROCESSABLE_ENTITY ],
            [ [1, 2, 8], Response::HTTP_UNPROCESSABLE_ENTITY ],
            [ [], Response::HTTP_UNPROCESSABLE_ENTITY ],
        ];
    }


    /**
     * @dataProvider dataProviderInconsistentWeekDays
     */
    public function testInconsistentWeekDays($weekDays, $expected)
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('PUT', "/api/patients/1/availability", ['json' => array_merge(
            self::AVAILABILITY_PATCH_ONE_DAY,
            [ 'weekDays' => $weekDays ]
        )]);
        $this->assertResponseStatusCodeSame($expected);
    }

    public function dataProviderTestMissingContent()
    {
        return [
            [ 'weekDays' ],
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

        $data = array_diff_key(self::AVAILABILITY_PATCH_ONE_DAY, [$content => null]);
        $crawler = $this->client->request('PUT', "/api/patients/1/availability", ['json' => $data]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    public function testDeleteAvailability()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('PUT', "/api/patients/1/availability", [
            'json' => [
                'weekDays' => [ 1 ],
                'start' => '1000',
                'end' => '1030',
                'available' => false,
            ],
        ]);
        $this->assertResponseIsSuccessful();

        // Lecture de la disponibilité du patient pour vérification de la suppression
        $crawler = $this->client->request('GET', "/api/patients/1");
        $this->assertJsonContains([
            'availability' => [
                '1' => [
                    [1030, 1200],
                ],
            ],
        ]);
    }
}

<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

class PatientControllerTest extends AbstractControllerTestCase
{
    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/patient.yaml',
            __DIR__ . '/../../fixtures/tests/care_request.yaml',
        ]);
    }    

    public function testGetPatientAsAnonymous()
    {
        $crawler = $this->client->request('GET', "/patients/1");
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND); // Redirection vers le login
        $this->assertResponseRedirects('/login');
    }

    public function dataProviderGetPatient()
    {
        return [
            [ 1, Response::HTTP_OK ],
            [ 99, Response::HTTP_NOT_FOUND ],
        ];
    }

    /** 
     * @dataProvider dataProviderGetPatient
     */
    public function testGetPatient($patientId, $expected)
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', "/patients/$patientId");
        $this->assertResponseStatusCodeSame($expected);
    }


    public function testGetExistingPatient()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', "/patients/1");
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Patient patient_1_firstname patient_1_lastname');
        $this->assertCount(3, $crawler->filter('#care-requests-accordion h3')); // Nombre de care requests du patient
    }

    
    public function dataProviderGetAsDoctor()
    {
        return [
            [ 'user1@example.com', Response::HTTP_OK ],
            [ 'user2@example.com', Response::HTTP_FORBIDDEN ],
        ];
    }

    /**
     * @dataProvider dataProviderGetAsDoctor
     */
    public function testGetAsDoctor($doctorEmail, $expected)
    {
        $this->loginUser($doctorEmail);
        $crawler = $this->client->request('GET', "/patients/1");
        $this->assertResponseStatusCodeSame($expected);
    }
}

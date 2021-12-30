<?php

namespace App\Tests\Controller;

use App\Repository\PatientRepository;
use Symfony\Component\HttpFoundation\Response;

class PatientControllerTest extends AbstractControllerTestCase
{
    private PatientRepository $patientRepository;
    
    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/patient.yaml',
            __DIR__ . '/../../fixtures/tests/care_request.yaml',
        ]);
        
        $this->patientRepository = static::getContainer()->get(PatientRepository::class);
    }    
    

    public function testNew()
    {
        $previousPatientCount = count($this->patientRepository->findAll());

        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', '/patients/new');
        $this->assertSelectorExists("form[name='patient']");
        
        $this->client->submitForm('Ajouter', [
            'patient[lastname]' => 'new_lastname',
            'patient[phone]' => 'new_phone',
        ]);
        $this->assertCount($previousPatientCount + 1, $this->patientRepository->findAll());
    }
    

    public function testForm()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', '/patient_forms/1');
        $this->assertSelectorExists("form[name='patient']");
        $this->assertInputValueSame('patient[firstname]', 'patient_1_firstname');
    }
    

    public function dataProviderAsAnonymous()
    {
        return [
            [ '/patients/1' ],
            [ '/patients/new' ],
            [ '/patient_forms/1' ],
        ];
    }
    
    /**
     * @dataProvider dataProviderAsAnonymous
     */
    public function testAsAnonymous($url)
    {
        $crawler = $this->client->request('GET', $url);
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
        $this->assertPageTitleSame('LAO | patient_1_firstname patient_1_lastname');
        $this->assertCount(4, $crawler->filter('#care-requests-accordion h3')); // Nombre de care requests du patient
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


    /**
     * @dataProvider dataProviderGetAsDoctor
     */
    public function testGetFormAsDoctor($doctorEmail, $expected)
    {
        $this->loginUser($doctorEmail);
        $crawler = $this->client->request('GET', "/patient_forms/1");
        $this->assertResponseStatusCodeSame($expected);
    }
        
        
    public function testGetShowCareRequest()
    {
        $this->loginUser('user1@example.com');
        // On demande d'afficher la care request 2 alors qu'elle est archivée
        $crawler = $this->client->request('GET', '/patients/1?careRequest=2');
        
        $this->assertSelectorNotExists('#care-requests-accordion #care-request-body-1.collapse.show');
        $this->assertSelectorExists('#care-requests-accordion #care-request-body-2.collapse.show');
    }
    

    
    public function testGetPatientWithoutCareRequest()
    {
        $this->loginUser('user1@example.com');
        // Lors de l'affichage d'un patient sans demande, on doit afficher
        // le formulaire de création de demande
        $crawler = $this->client->request('GET', '/patients/4');

        $this->assertSelectorTextSame("form[name='care_request'] button[name='care_request[upsert]']", 'Ajouter');
    }    
}

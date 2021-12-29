<?php

namespace App\Tests\Controller;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

class CareRequestControllerFormTest extends AbstractControllerTestCase
{
    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/care_request.yaml',
            __DIR__ . '/../../fixtures/tests/comment.yaml',
        ]);
    }    
    

    public function testNewCareRequestForm()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', '/patients/1/care_request_forms/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.accordion-header button', (new DateTimeImmutable())->format('d/m/Y'));
        $this->assertSelectorExists("form[name='care_request']");
    }


    public function dataProviderNewCareRequestFormPatient()
    {
        return [
            [ 99, Response::HTTP_NOT_FOUND ], // patient inexistant
            [ 3, Response::HTTP_FORBIDDEN ],  // patient autre cabinet
        ];
    }
    
    /**
     * @dataProvider dataProviderNewCareRequestFormPatient
     */
    public function testNewCareRequestFormPatient($patientId, $expected)
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', sprintf('/patients/%d/care_request_forms/new', $patientId));
        $this->assertResponseStatusCodeSame($expected);
    }

    public function dataProviderGetCareRequest()
    {
        return [
            [1, Response::HTTP_OK],
            [99, Response::HTTP_NOT_FOUND],
        ];
    }
    /**
     * @dataProvider dataProviderGetCareRequest
     */
    public function testGetCareRequest(int $id, int $expectedStatus)
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', "/care_request_forms/$id");

        $this->assertResponseStatusCodeSame($expectedStatus);
    }

    public function testGetCareRequestAsAnonymous()
    {
        $crawler = $this->client->request('GET', "/care_request_forms/1");
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND); // Redirection vers le login
        $this->assertResponseRedirects('/login');
    }

    public function dataProviderGetCareRequestAsDoctor()
    {
        return [
            ['user1@example.com', Response::HTTP_OK],
            ['user2@example.com', Response::HTTP_FORBIDDEN],
        ];
    }
    /**
     * @dataProvider dataProviderGetCareRequestAsDoctor
     */
    public function testGetCareRequestAsDoctor(string $doctorEmail, int $expectedStatus)
    {
        $this->loginUser($doctorEmail);
        $crawler = $this->client->request('GET', "/care_request_forms/1");
        $this->assertResponseStatusCodeSame($expectedStatus);
    }

    public function testGetCareRequestContentActive()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', "/care_request_forms/1");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextSame('h3 button', 'Demande du 27/09/2021 Active Prioritaire');
        $this->assertSelectorExists('form');
        $this->assertFormValue('form', 'care_request[contactedBy]', '/api/doctors/1');
        $this->assertSelectorExists('form button[name="care_request[accept]"]');
        $this->assertSelectorNotExists('form button[name="care_request[accept]"][disabled]');
        $this->assertSelectorExists('form button[name="care_request[abandon]"]');
        $this->assertSelectorNotExists('form button[name="care_request[abandon]"][disabled]');
        $this->assertSelectorTextSame('form button[name="care_request[upsert]"]', 'Enregistrer');
        
        // Test de l'existence du commentaire
        $this->assertSelectorExists('section.comments ul.comments li');
        
        // Ce commentaire ne doit avoir une form (=> il est modifiable)
        $firstComment = $crawler->filter('section.comments ul.comments li')->first();
        $this->assertEquals(1, $firstComment->filter('form')->count());
    }

    public function testGetCareRequestContentActiveNonPrioritaire()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', "/care_request_forms/5");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextSame('h3 button', 'Demande du 25/09/2021 Active');
        $this->assertSelectorExists('form');
        $this->assertFormValue('form', 'care_request[contactedBy]', '/api/doctors/1');
        $this->assertSelectorExists('form button[name="care_request[accept]"]');
        $this->assertSelectorNotExists('form button[name="care_request[accept]"][disabled]');
        $this->assertSelectorExists('form button[name="care_request[abandon]"]');
        $this->assertSelectorNotExists('form button[name="care_request[abandon]"][disabled]');
        $this->assertSelectorTextSame('form button[name="care_request[upsert]"]', 'Enregistrer');
    }

    public function testGetCareRequestContentArchived()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', "/care_request_forms/2");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextSame('h3 button', 'Demande du 26/09/2021 Archivée Prioritaire Prise en charge par doctor_1_firstname le 28/09/2021');
        $this->assertSelectorExists('form');
        $this->assertSelectorTextSame('form button[name="care_request[reactivate]"]', 'Réactiver');
        
        // Test de l'existence du commentaire
        $this->assertSelectorExists('section.comments ul.comments li');
        
        // Ce commentaire ne doit pas avoir de form (=> il n'est pas modifiable)
        $firstComment = $crawler->filter('section.comments ul.comments li')->first();
        $this->assertEquals(0, $firstComment->filter('form')->count());
    }

    public function testGetCareRequestContentAbandoned()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', "/care_request_forms/3");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextSame('h3 button', "Demande du 25/09/2021 Abandonnée Prioritaire Abandonnée par praticien inconnu le 28/09/2021 (Raison d'abandon 1)");
        $this->assertSelectorExists('form');
        $this->assertSelectorTextSame('form button[name="care_request[reactivate]"]', 'Réactiver');
        
        // Test de l'existence du commentaire
        $this->assertSelectorExists('section.comments ul.comments li');
        
        // Ce commentaire ne doit pas avoir de form (=> il n'est pas modifiable)
        $firstComment = $crawler->filter('section.comments ul.comments li')->first();
        $this->assertEquals(0, $firstComment->filter('form')->count());
    }
}
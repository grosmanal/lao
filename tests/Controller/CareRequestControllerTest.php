<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

class CareRequestControllerTest extends AbstractControllerTest
{
    public function setUp(): void
    {
        $this->setUpTestController([__DIR__ . '/../../fixtures/tests/care_request.yaml']);
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
        $this->loginUser('admin@example.com');
        $crawler = $this->client->request('GET', "/care_request_form/$id");

        $this->assertResponseStatusCodeSame($expectedStatus);
    }

    public function testGetCareRequestAsAnonymous()
    {
        $crawler = $this->client->request('GET', "/care_request_form/1");
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
        $crawler = $this->client->request('GET', "/care_request_form/1");
        $this->assertResponseStatusCodeSame($expectedStatus);
    }

    public function testGetCareRequestContentActive()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', "/care_request_form/1");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextSame('h3 button', 'Demande du 27/09/2021 active'); // TODO traduction
        $this->assertSelectorExists('form');
        $this->assertFormValue('form', 'care-request-id', '1');
        $this->assertFormValue('form', 'care_request[doctorCreator]', '1');
        $this->assertSelectorExists('form button[name~="care_request[acceptAction]"]');
        $this->assertSelectorNotExists('form button[name~="care_request[acceptAction]"][disabled]');
        $this->assertSelectorExists('form button[name~="care_request[abandonAction]"]');
        $this->assertSelectorNotExists('form button[name~="care_request[abandonAction]"][disabled]');
        $this->assertSelectorTextSame('form button[type="submit"]', 'Enregistrer');
    }

    public function testGetCareRequestContentArchived()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', "/care_request_form/2");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextSame('h3 button', 'Demande du 27/09/2021 archived'); // TODO traduction
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('form button[name~="care_request[acceptAction]"][disabled]');
        $this->assertSelectorExists('form button[name~="care_request[abandonAction]"][disabled]');
        $this->assertSelectorTextSame('form button[type="submit"]', 'Réactiver');
    }

    public function testGetCareRequestContentAbandonned()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', "/care_request_form/3");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextSame('h3 button', 'Demande du 27/09/2021 abandoned'); // TODO traduction
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('form button[name~="care_request[acceptAction]"][disabled]');
        $this->assertSelectorExists('form button[name~="care_request[abandonAction]"][disabled]');
        $this->assertSelectorTextSame('form button[type="submit"]', 'Réactiver');
    }
}
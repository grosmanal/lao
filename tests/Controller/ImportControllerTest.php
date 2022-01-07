<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

class ImportControllerTest extends AbstractControllerTestCase
{
    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/complaint.yaml',
        ]);
    }

    public function testAsAnonymous()
    {
        $this->client->request('GET', '/import');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND); // Redirection vers le login
        $this->assertResponseRedirects('/login');
    }

    public function testAsAdmin()
    {
        $this->loginUser('admin@example.com');
        $this->client->request('GET', '/import');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testImport()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', '/import');
        $this->assertResponseIsSuccessful();

        // Soumission du formulaire
        $submitButton = $crawler->selectButton('Submit');
        $form = $submitButton->form();

        /** @var Symfony\Component\DomCrawler\Field\FileFormField */
        $fileType = $form['import[file]'];

        $fileType->upload(
            __DIR__ . '/../../fixtures/tests/importService/data.ods'
        );

        $crawler = $this->client->submit($form);
        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextSame('.alert-success', '2 patients créés');
    }

    public function testImportWithErrors()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', '/import');
        $this->assertResponseIsSuccessful();

        // Soumission du formulaire
        $submitButton = $crawler->selectButton('Submit');
        $form = $submitButton->form();

        /** @var Symfony\Component\DomCrawler\Field\FileFormField */
        $fileType = $form['import[file]'];

        $fileType->upload(
            __DIR__ . '/../../fixtures/tests/importService/data_errors.ods'
        );

        $crawler = $this->client->submit($form);
        $this->assertSelectorExists('.alert-danger');
    }
}

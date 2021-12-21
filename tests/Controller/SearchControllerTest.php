<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

class SearchControllerTest extends AbstractControllerTestCase
{
    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/care_request.yaml',
        ]);
    }
    
    public function testAsAnonymous()
    {
        $this->client->request('GET', '/search');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/login');
    }
    
    
    public function testSearch()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', '/search');
        $this->assertResponseIsSuccessful();
    
        // Un formulaire doit être affiché
        $this->assertSelectorExists("form[name='search']");
        $this->assertSelectorNotExists('section.search-result');

        // Soumission du formulaire
        $crawler = $this->client->submitForm('Rechercher', [
            'search[label]' => 'patient',
        ], 'GET');
        
        // Il doit y avoir 1 résultat
        $this->assertSelectorExists('section.search-result');
        $this->assertCount(3, $crawler->filter('section.search-result table tbody > tr'));
    }
    
    
    public function dataProviderUnconsistentInput()
    {
        return [
            [ 1, '08:00', '18:00', true],
            [ null, '08:00', '18:00', false],
            [ 1, null, '18:00', false],
            [ 1, '08:00', null, false],
        ];
    }

    /**
     * @dataProvider dataProviderUnconsistentInput
     */
    public function testUnconsistentInput(?int $weekDay, ?string $timeStart, ?string $timeEnd, bool $expectResults)
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', '/search');
        $this->assertResponseIsSuccessful();
        
        $formFields = [];
        if ($weekDay) {
            $formFields['search[weekDay]'] = $weekDay;
        }
        if ($timeStart) {
            $formFields['search[timeStart]'] = $timeStart;
        }
        if ($timeEnd) {
            $formFields['search[timeEnd]'] = $timeEnd;
        }

        // Soumission du formulaire
        $crawler = $this->client->submitForm('Rechercher', $formFields, 'GET');
        
        if ($expectResults) {
            $this->assertSelectorExists('section.search-result');
        } else {
            $this->assertSelectorNotExists('section.search-result');
        }
    }
}
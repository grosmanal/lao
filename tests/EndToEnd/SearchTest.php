<?php

namespace App\Tests\EndToEnd;

/**
 * @group end2end
 */
class SearchTest extends AbstractEndToEndTestCase
{
    public function setUp(): void
    {
        $this->setUpTestPanther();
    }    
    
    /**
     * Recherche et clic sur résultat
     */
    public function testSearch(): void
    {
        $this->loginUser('user1@example.com');

        $crawler = $this->client->request('GET', '/search');
        $this->assertPageTitleSame('LAO | Recherche');
         
        $resultsSelector = 'section.search-result';
        $this->assertSelectorNotExists($resultsSelector);

        // Soumission du formulaire patient
        $crawler = $this->client->submitForm('Rechercher', [
            'search[label]' => 'patient_2',
        ]);
        $this->assertSelectorIsVisible($resultsSelector);
        
        // Click sur un résultat ouvre la page du patient
        $crawler = $this->client->clickLink('patient_2_firstname patient_2_lastname');
        $this->assertPageTitleSame('LAO | patient_2_firstname patient_2_lastname');
    }
}
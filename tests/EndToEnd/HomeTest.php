<?php

namespace App\Tests\EndToEnd;

class HomeTest extends AbstractEndToEndTestCase
{
    public function setUp(): void
    {
        $this->setUpTestPanther();
    }

    /**
     * Cliquer sur la fermeture des articles doit les supprimer du DOM
     */
    public function testMarkArticle(): void
    {
        $this->loginUser('user1@example.com');

        $crawler = $this->client->request('GET', '/home');
        $this->assertPageTitleContains('Liste attente Ortho');
        
        // La liste des articles existe
        $this->assertSelectorIsVisible('section.articles ul.articles-list > li:first-child');
        $this->assertSelectorIsNotVisible('section.articles ul.articles-list > li:nth-child(2)');
        
        // Click sur le bouton de fermeture du premier article
        $firstArticleItemSelector = 'section.articles ul.articles-list > li:first-child article';
        $this->client->executeScript(sprintf("document.querySelector('%s').click()", $firstArticleItemSelector . ' button'));
        //sleep(1);
        $this->client->waitForStaleness($firstArticleItemSelector);
        $crawler = $this->client->refreshCrawler();
        $this->assertCount(1, $crawler->filter('section.articles ul.articles-list > li'));

        // Click sur le bouton de fermeture du second article (désormais premier)
        $this->client->executeScript(sprintf("document.querySelector('%s').click()", $firstArticleItemSelector . ' button'));
        $this->client->waitForStaleness($firstArticleItemSelector);
        $crawler = $this->client->refreshCrawler();
        $this->assertSelectorNotExists('section.articles');
    }
    
    /**
     * Cliquer sur un patient en anomalie doit charger la page du patient
     */
    public function testGoAnomalyPatient(): void
    {
        $this->loginUser('user1@example.com');

        $crawler = $this->client->request('GET', '/home');
        
        $patientAnomalySelector = 'section.patients-anomaly ul > li';
        $this->assertSelectorIsVisible($patientAnomalySelector);
        
        // Click sur le premier patient en anomalie
        $crawler = $this->client->click($crawler->filter($patientAnomalySelector . ':first-child a')->link());
        
        // On doit être sur la page du patient 4
        $this->assertPageTitleSame('Patient patient_4_firstname patient_4_lastname');
    }

    /**
     * Cliquer sur le lien "Afficher l'activité pour les 30 derniers jours" change le titre du card de la section
     */
    public function testShowMoreActivity(): void
    {
        $this->loginUser('user1@example.com');

        $crawler = $this->client->request('GET', '/home');
        $titreActiviteSelector = 'section.activity h2';
        $this->assertSelectorTextSame($titreActiviteSelector, 'Activité depuis 7 jours');
        
        $crawler = $this->client->clickLink('Afficher l’activité des 30 derniers jours');
        $this->assertSelectorTextSame($titreActiviteSelector, 'Activité depuis 30 jours');
    }
}
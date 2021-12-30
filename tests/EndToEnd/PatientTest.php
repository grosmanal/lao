<?php

namespace App\Tests\EndToEnd;

/**
 * @group end2end
 */
class PatientTest extends AbstractEndToEndTestCase
{
    public function setUp(): void
    {
        $this->setUpTestPanther();
    }    
    
    /**
     * Création d'un patient et de sa demande
     */
    public function testCreation(): void
    {
        $this->loginUser('user1@example.com');

        $crawler = $this->client->request('GET', '/patients/new');
        $this->assertPageTitleContains('LAO | Nouveau patient');
         
        $careRequestSectionSelector = 'section.care-request-section';
        $this->assertSelectorNotExists($careRequestSectionSelector);

        // Soumission du formulaire patient
        $crawler = $this->client->submitForm('Ajouter', [
            'patient[firstname]' => 'new_patient_firstname',
            'patient[lastname]' => 'new_patient_lastname',
            'patient[birthdate]' => $this->toFormDate('2010-05-01'),
            'patient[contact]' => 'new_patient_contact',
            'patient[phone]' => '01 02 03 04 05',
            'patient[email]' => 'new-patient@example.com',
        ]);
        
        $this->assertSelectorIsVisible($careRequestSectionSelector);

        $careRequestFormSelector = $careRequestSectionSelector . " form[name='care_request']";
        $this->assertCount(1, $crawler->filter($careRequestFormSelector));
        
        $newCommentFormSelector = $careRequestSectionSelector . " .accordion-body .comment-form";
        $this->assertSelectorNotExists($newCommentFormSelector);

        // Soumission du formulaire demande
        $careRequestSubmitButtonSelector = $careRequestFormSelector . " button[name='care_request[upsert]']";
        $crawler->filter($careRequestFormSelector)->form([
            'care_request[contactedBy]' => '/api/doctors/1',
            'care_request[contactedAt]' => $this->toFormDate('2021-12-22'),
            'care_request[complaint]' => '/api/complaints/1',
            'care_request[priority]' => 1,
        ]);
        $javascriptAction = sprintf("document.querySelector('%s').click()", addslashes($careRequestSubmitButtonSelector));
        $this->client->executeScript($javascriptAction);
        $this->client->waitForElementToContain($careRequestSubmitButtonSelector, 'Enregistrer', 2);
        
        // Le formulaire des commentaires doit être visible
        $this->assertSelectorIsVisible($newCommentFormSelector);
        
        // Création d'un commentaire
        // Je ne sais pas comment ajouter un commentaire : ce n'est pas un formulaire
        /*
        $this->client->submit($crawler->filter($newCommentFormSelector)->form(), [
            'comment[content]' => 'lorem ipsum',
        ]);
        */
        
        $careRequestContactedByFieldSelector = $careRequestFormSelector . " select[name='care_request[contactedBy]']";
        $careRequestReactivateButtonSelector = $careRequestFormSelector . " button[name='care_request[reactivate]']";
        $careRequestAcceptButtonSelector = $careRequestFormSelector . " button[name='care_request[accept]']";
        $careRequestAbandonButtonSelector = $careRequestFormSelector . " button[name='care_request[abandon]']";

        // Prise en charge de la demande
        $javascriptAction = sprintf("document.querySelector('%s').click()", addslashes($careRequestAcceptButtonSelector));
        $this->client->executeScript($javascriptAction);
        $crawler = $this->client->waitForElementToContain($careRequestReactivateButtonSelector, 'Réactiver', 2);
        $this->assertSelectorIsDisabled($careRequestContactedByFieldSelector);
        
        // Réactivaviton de la demande
        $javascriptAction = sprintf("document.querySelector('%s').click()", addslashes($careRequestReactivateButtonSelector));
        $this->client->executeScript($javascriptAction);
        $crawler = $this->client->waitForElementToContain($careRequestSubmitButtonSelector, 'Enregistrer', 2);
        $this->assertSelectorIsEnabled($careRequestContactedByFieldSelector);
        
        // Abandon de la demande
        $careRequestConfirmButtonI = $careRequestFormSelector . " button i.bi-exclamation-diamond";
        $javascriptAction = sprintf("document.querySelector('%s').click()", addslashes($careRequestAbandonButtonSelector));
        $this->client->executeScript($javascriptAction);
        $this->assertSelectorIsVisible($careRequestConfirmButtonI); // Icone du bouton de confirmation
        $this->client->waitForStaleness($careRequestConfirmButtonI);
        //$crawler = $this->client->refreshCrawler();
        $crawler->filter($careRequestFormSelector)->form([
            'care_request[abandonedReason]' => '/api/abandon_reasons/1',
        ]);
        $this->client->executeScript($javascriptAction);
        $crawler = $this->client->waitForElementToContain($careRequestReactivateButtonSelector, 'Réactiver', 2);
        $this->assertSelectorIsDisabled($careRequestContactedByFieldSelector);
    }
}
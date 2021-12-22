<?php

namespace App\Tests\EndToEnd;

class NotificationTest extends AbstractEndToEndTestCase
{
    public function setUp(): void
    {
        $this->setUpTestPanther();
    }    
    
    const NOTIFICATION_SELECTOR = 'section.notifications';
    
    /**
     * Marquage d'une notification à lue
     */
    public function testMarkNotification(): void
    {
        $this->loginUser('user1@example.com');

        $crawler = $this->client->request('GET', '/notifications');
        $this->assertPageTitleSame('Notifications');
         
        $this->assertSelectorExists(self::NOTIFICATION_SELECTOR);
        
        // Il doit à avoir un seul notification non lue
        $notificationItemsSelector = self::NOTIFICATION_SELECTOR . " ul.notifications > li";
        $this->assertCount(1, $crawler->filter($notificationItemsSelector));

        // Clic sur le bouton pour marquer la notification
        $markButtonSelector = $notificationItemsSelector . ":first-child button[name='mark']";
        $javascriptAction = sprintf("document.querySelector('%s').click()", addslashes($markButtonSelector));
        $this->client->executeScript($javascriptAction);

        // Il ne doit y avoir plus aucune notification
        $this->client->waitForStaleness($notificationItemsSelector);
        $this->assertSelectorNotExists($notificationItemsSelector);
        
        // Recharchement de la page
        $crawler = $this->client->request('GET', '/notifications');
        
        // On doit voir le warning
        $this->assertSelectorIsVisible(self::NOTIFICATION_SELECTOR . " p.alert-warning");
    }
    
    public function testMarkAll(): void
    {
        $this->loginUser('user5@example.com');
        
        $crawler = $this->client->request('GET', '/notifications');
        $this->assertPageTitleSame('Notifications');
        //
        // Il doit à avoir deux notifications non lues
        $notificationItemsSelector = self::NOTIFICATION_SELECTOR . " ul.notifications > li";
        $this->assertCount(2, $crawler->filter($notificationItemsSelector));
        
        $crawler = $this->client->click($crawler->filter(self::NOTIFICATION_SELECTOR . " h2 a")->link());
        
        // On doit voir le warning
        $this->assertSelectorIsVisible(self::NOTIFICATION_SELECTOR . " p.alert-warning");
    }
}
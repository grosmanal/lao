<?php

namespace App\Tests\Controller;

use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Response;

class NotificationControllerTest extends AbstractControllerTestCase
{
    private NotificationRepository $notificationRepository;

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/notification.yaml',
        ]);
        
        $this->notificationRepository = static::getContainer()->get(NotificationRepository::class);
    }
        
    public function dataProviderUrl()
    {
        return [
            [ '/notifications', ],
            [ '/notifications_read' ],
        ];
    }
    
    /**
     * @dataProvider dataProviderUrl
     */
    public function testAsAnonymous($url)
    {
        $this->client->request('GET', $url);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND); // Redirection vers le login
        $this->assertResponseRedirects('/login');
    }

    /**
     * @dataProvider dataProviderUrl
     */
    public function testAsAdmin($url)
    {
        $this->loginUser('admin@example.com');
        $this->client->request('GET', $url);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    
    public function testUnread()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', '/notifications');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('section.notifications');
        $this->assertCount(1, $crawler->filter('section.notifications ul.notifications > li'));
    }
    
    public function testNoUnread()
    {
        $this->loginUser('user5@example.com');
        $crawler = $this->client->request('GET', '/notifications');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('section.notifications');
        $this->assertSelectorExists('section.notifications .card-body p.alert-warning');
    }
    
    public function testRead()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', '/notifications_read');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('section.notifications');
        $this->assertCount(2, $crawler->filter('section.notifications ul.notifications > li'));
    }
    
    public function testMarkAll()
    {
        $this->loginUser('user1@example.com');
        $this->client->request('GET', '/notifications_mark_all');
        $this->assertResponseRedirects('/notifications');
        
        $doctor = $this->getUserAsDoctor('user1@example.com');
        $this->assertCount(0, $this->notificationRepository->findUnreadForDoctor($doctor));
    }
}
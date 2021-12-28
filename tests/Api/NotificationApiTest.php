<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class NotificationApiTest extends AbstractApiTestCase
{
    const NOTIFICATION_DATA = [
        'comment' => '/api/comments/1',
        'doctor' => '/api/doctors/1',
        'createdAt' => 'now',
    ];

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/notification.yaml',
        ]);
    }
    
    
    /**
     * Test du contenu retourné
     */
    public function testGet()
    {
        $this->loginUser('admin@example.com');
        $this->client->request('GET', "/api/notifications/1");
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        
        $this->assertJsonContains([
            'comment' => '/api/comments/7',
            'doctor' => '/api/doctors/1',
            'createdAt' => '2021-09-28T15:32:00+00:00',
        ]);
    }


    public function dataProviderGetAllAs()
    {
        return [
            ['admin@example.com', [
                '/api/notifications/1',
                '/api/notifications/2',
                '/api/notifications/3',
            ]],
            ['user1@example.com', [
                '/api/notifications/1',
                '/api/notifications/2',
                '/api/notifications/3',
            ]],
            ['user5@example.com', [
            ]],
            ['user2@example.com', [
            ]],
        ];
    }

    /**
     * @dataProvider dataProviderGetAllAs
     */
    public function testGetAllAs($userEmail, $expectedNotificationApiIds)
    {
        $this->loginUser($userEmail);
        $this->client->getKernelBrowser()->followRedirects();
        $this->client->request('GET', "/api/notifications/");
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['hydra:totalItems' => count($expectedNotificationApiIds)]);

        // Constitution de la liste des care requests récupérées
        $notificationApiIds = [];
        foreach (json_decode($this->client->getResponse()->getContent(), true)['hydra:member'] as $notification) {
            $notificationApiIds[] = $notification['@id'];
        }
        $this->assertSame($expectedNotificationApiIds, $notificationApiIds);
    }


    public function dataProviderGetAs()
    {
        return [
            ['admin@example.com', Response::HTTP_OK],
            ['user1@example.com', Response::HTTP_OK],
            ['user2@example.com', Response::HTTP_FORBIDDEN],
            ['user3@example.com', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @dataProvider dataProviderGetAs
     */
    public function testGetAsDoctor ($userEmail, $expected)
    {
        $this->loginUser($userEmail);
        $this->client->request('GET', "/api/notifications/1");
        $this->assertResponseStatusCodeSame($expected);
    }
    
    
    public function dataProviderPost()
    {
        return [
            [ 'admin@example.com', Response::HTTP_CREATED ],
            [ 'user1@example.com', Response::HTTP_FORBIDDEN ],
            [ 'user2@example.com', Response::HTTP_FORBIDDEN ],
        ];
    }

    /**
     * @dataProvider dataProviderPost
     */
    public function testPost($userEmail, $expected)
    {
        $this->loginUser($userEmail);
        $crawler = $this->client->request('POST', "/api/notifications", [
            'json' => self::NOTIFICATION_DATA,
        ]);
        $this->assertResponseStatusCodeSame($expected);

        if ($expected == Response::HTTP_CREATED) {
            $notificationApiId = json_decode($crawler->getContent(), true)['@id'];
            $this->client->request('GET', $notificationApiId);
            $this->assertResponseIsSuccessful();
        }
    }
    

    public function dataProviderAnotherOfficeData()
    {
        return [
            ['comment', '/api/comments/4', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['doctor', '/api/doctors/2', Response::HTTP_UNPROCESSABLE_ENTITY],
        ];
    }

    /**
     * @dataProvider dataProviderAnotherOfficeData
     */
    public function testAnotherOfficeData($payloadKey, $payloadValue, $expected)
    {
        $this->loginUser('admin@example.com');
        $data = array_merge(self::NOTIFICATION_DATA, [$payloadKey => $payloadValue]);
        $crawler = $this->client->request('POST', "/api/notifications", [
            'json' => $data,
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }
    

    public function dataProviderDeleteAs()
    {
        return [
            ['admin@example.com', Response::HTTP_NO_CONTENT],
            ['user1@example.com', Response::HTTP_FORBIDDEN],
            ['user2@example.com', Response::HTTP_FORBIDDEN],
        ];
    }
    
    /**
     * @dataProvider dataProviderDeleteAs
     */
    public function testDeleteAs($userEmail, $expected)
    {
        // On peut effacer que ses propres commentaires
        $this->loginUser($userEmail);
        $crawler = $this->client->request('DELETE', "/api/notifications/1");
        $this->assertResponseStatusCodeSame($expected);
    }
    

    public function dataProviderPutAs()
    {
        return [
            ['admin@example.com', Response::HTTP_OK],
            ['user1@example.com', Response::HTTP_OK],
            ['user2@example.com', Response::HTTP_FORBIDDEN],
            ['user5@example.com', Response::HTTP_FORBIDDEN],
        ];
    }
    
    /**
     * @dataProvider dataProviderPutAs
     */
    public function testPutAs($userEmail, $expected)
    {
        // On ne peut modifier que ses notifications
        // 
        $readAt = '2021-12-06T09:24:00+00:00';

        $this->loginUser($userEmail);
        $crawler = $this->client->request('PUT', "/api/notifications/1", [
            'json' => [
                'readAt' => $readAt,
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);

        if ($expected == Response::HTTP_OK) {
            $crawler = $this->client->request('GET', "/api/notifications/1");
            $this->assertJsonContains(['readAt' => $readAt]);
        }
    }
    
    
    public function dataProviderPutUnupdatableFields()
    {
        return [
            [ 'comment', '/api/comments/1', '/api/comments/7' ], // pas modifiable
            [ 'doctor', '/api/doctors/3', '/api/doctors/1' ], // pas modifiable
            [ 'createdAt', '2000-01-01T00:00:00+00:00', '2021-09-28T15:32:00+00:00'], // pas modifiable
            [ 'readAt', '2021-10-01T17:30:52+00:00', '2021-10-01T17:30:52+00:00'], // modifiable
        ];
    }

    /**
     * @dataProvider dataProviderPutUnupdatableFields
     */
    public function testPutUnupdatableFields($payloadKey, $payloadValue, $expected)
    {
        // On ne peut pas modifier :
        // - le commentaire
        // - le user
        // d'une notification
        
        $this->loginUser('admin@example.com');
        $crawler = $this->client->request('PUT', "/api/notifications/1", [
            'json' => [ $payloadKey => $payloadValue ],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([ $payloadKey => $expected ]);
    }
}
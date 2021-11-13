<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class NotificationApiTest extends AbstractApiTestCase
{
    const NOTIFICATION_DATA = [
        'comment' => '/api/comments/1',
        'doctor' => '/api/doctors/1',
        'state' => 'new',
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
            'comment' => '/api/comments/9',
            'doctor' => '/api/doctors/1',
            'state' => 'viewed',
        ]);
    }


    public function dataProviderGetAllAs()
    {
        return [
            ['admin@example.com', [
                '/api/notifications/1',
            ]],
            ['user1@example.com', [
                '/api/notifications/1',
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
        $newState = 'archived';

        $this->loginUser($userEmail);
        $crawler = $this->client->request('PUT', "/api/notifications/1", [
            'json' => [
                'state' => $newState,
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);

        if ($expected == Response::HTTP_OK) {
            $crawler = $this->client->request('GET', "/api/notifications/1");
            $this->assertJsonContains(['state' => $newState]);
        }
    }
    
    
    public function dataProviderPutUnupdatableFields()
    {
        return [
            [ 'comment', '/api/comments/1', '/api/comments/9' ], // pas modifiable
            [ 'doctor', '/api/doctors/3', '/api/doctors/1' ], // pas modifiable
            [ 'state', 'viewed', 'viewed'], // modifiable
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
    
    public function dataProviderPutUnconsistentValues()
    {
        return [
            [ 'state', \App\Entity\Notification::STATE_NEW, Response::HTTP_OK ],
            [ 'state', \App\Entity\Notification::STATE_VIEWED, Response::HTTP_OK ],
            [ 'state', \App\Entity\Notification::STATE_ARCHIVED, Response::HTTP_OK ],
            [ 'state', '', Response::HTTP_UNPROCESSABLE_ENTITY ],
            [ 'state', 'unknown_state', Response::HTTP_UNPROCESSABLE_ENTITY ],
        ];
    }
    
    /**
     * @dataProvider dataProviderPutUnconsistentValues
     */
    public function testPutUnconsistentValues($payloadKey, $payloadValue, $expected)
    {
        $this->loginUser('admin@example.com');
        $crawler = $this->client->request('PUT', "/api/notifications/1", [
            'json' => [ $payloadKey => $payloadValue ],
        ]);
        
        $this->assertResponseStatusCodeSame($expected);
    }
    

}
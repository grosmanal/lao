<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class CommentApiTest extends AbstractApiTestCase
{
    const COMMENT_DATA = [
        'author' => '/api/doctors/1',
        'careRequest' => '/api/care_requests/1',
        'creationDate' => '2021-10-29',
        'content' => 'content new comment',
    ];

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/comment.yaml',
        ]);
    }
    
    // Test du contenu de commentaire retourné
    public function testGet()
    {
        $this->loginUser('admin@example.com');
        $this->client->request('GET', "/api/comments/1");
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            'author' => [
                '@id' => '/api/doctors/1',
                '@type' => 'Doctor',
                'firstname' => 'doctor_1_firstname',
                'lastname' => 'doctor_1_lastname',
            ],
            'careRequest' => [
                '@id' => '/api/care_requests/1',
                'creationDate' => '2021-09-27T00:00:00+00:00',
                'patient' => [
                    '@id' => '/api/patients/1',
                    'firstname' => 'patient_1_firstname',
                    'lastname' => 'patient_1_lastname',
                ],
                'state' => 'active',
                
            ],
            'creationDate' => '2021-09-27T00:00:00+00:00',
            'content' => 'lorem ipsum comment_1',
        ]);
    }

    public function dataProviderGetAllAs()
    {
        return [
            ['admin@example.com', [
                '/api/comments/1',
                '/api/comments/2',
                '/api/comments/3',
                '/api/comments/4',
            ]],
            ['user1@example.com', [
                '/api/comments/1',
                '/api/comments/2',
                '/api/comments/3',
            ]],
            ['user5@example.com', [
                '/api/comments/1',
                '/api/comments/2',
                '/api/comments/3',
            ]],
            ['user2@example.com', [
                '/api/comments/4',
            ]],
        ];
    }

    /**
     * @dataProvider dataProviderGetAllAs
     */
    public function testGetAllAs($userEmail, $expectedCommentApiIds)
    {
        $this->loginUser($userEmail);
        $this->client->getKernelBrowser()->followRedirects();
        $this->client->request('GET', "/api/comments/");
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['hydra:totalItems' => count($expectedCommentApiIds)]);

        // Constitution de la liste des care requests récupérées
        $commentApiIds = [];
        foreach (json_decode($this->client->getResponse()->getContent(), true)['hydra:member'] as $comment) {
            $commentApiIds[] = $comment['@id'];
        }
        $this->assertSame($expectedCommentApiIds, $commentApiIds);
    }
    
    
    public function dataProviderGetAsDoctor()
    {
        return [
            ['user1@example.com', Response::HTTP_OK],
            ['user2@example.com', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @dataProvider dataProviderGetAsDoctor
     */
    public function testGetAsDoctor ($userEmail, $expected)
    {
        $this->loginUser($userEmail);
        $this->client->request('GET', "/api/comments/1");
        $this->assertResponseStatusCodeSame($expected);
    }
    
    
    public function testPost()
    {
        $this->loginUser('admin@example.com');
        $crawler = $this->client->request('POST', "/api/comments", [
            'json' => self::COMMENT_DATA,
        ]);
        $this->assertResponseIsSuccessful();
        $commentApiId = json_decode($crawler->getContent(), true)['@id'];
        $this->client->request('GET', $commentApiId);
        $this->assertResponseIsSuccessful();
    }


    public function dataProviderPostMissingContent()
    {
        return [
            ['author', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['creationDate', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['careRequest', Response::HTTP_UNPROCESSABLE_ENTITY],
            ['content', Response::HTTP_UNPROCESSABLE_ENTITY],
        ];
    }

    /**
     * @dataProvider dataProviderPostMissingContent
     */
    public function testPostMissingContent($content, $expected)
    {
        $this->loginUser('admin@example.com');
        $crawler = $this->client->request('POST', "/api/comments", [
            'json' => array_diff_key(self::COMMENT_DATA, [ $content => null ]),
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }


    public function dataProviderAnotherOfficeData()
    {
        return [
            ['user1@example.com', 'careRequest', '/api/care_requests/1', Response::HTTP_CREATED],
            ['user1@example.com', 'careRequest', '/api/care_requests/2', Response::HTTP_CREATED],
            ['user1@example.com', 'careRequest', '/api/care_requests/4', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @dataProvider dataProviderAnotherOfficeData
     */
    public function testAnotherOfficeData($userEmail, $payloadKey, $payloadValue, $expected)
    {
        $this->loginUser($userEmail);
        $data = array_merge(self::COMMENT_DATA, [$payloadKey => $payloadValue]);
        $crawler = $this->client->request('POST', "/api/comments", [
            'json' => $data,
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }
    

    public function dataProviderPostAnotherAuthor()
    {
        return [
            ['admin@example.com', Response::HTTP_CREATED],
            ['user1@example.com', Response::HTTP_CREATED],
            ['user2@example.com', Response::HTTP_FORBIDDEN],
            ['user5@example.com', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @dataProvider dataProviderPostAnotherAuthor
     */
    public function testPostAnotherAuthor($userEmail, $expected)
    {
        $this->loginUser($userEmail);
        $crawler = $this->client->request('POST', "/api/comments", [
            'json' => self::COMMENT_DATA,
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }
    
    
    public function dataProviderDeleteAs()
    {
        return [
            ['admin@example.com', Response::HTTP_NO_CONTENT],
            ['user1@example.com', Response::HTTP_NO_CONTENT],
            ['user2@example.com', Response::HTTP_FORBIDDEN],
            ['user5@example.com', Response::HTTP_FORBIDDEN],
        ];
    }
    
    /**
     * @dataProvider dataProviderDeleteAs
     */
    public function testDeleteAs($userEmail, $expected)
    {
        // On peut effacer que ses propres commentaires
        $this->loginUser($userEmail);
        $crawler = $this->client->request('DELETE', "/api/comments/1");
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
        // On ne peut modifier que ses propres commentaires
        $this->loginUser($userEmail);
        $crawler = $this->client->request('PUT', "/api/comments/1", [
            'json' => [
                'content' => 'new content',
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }
    
    public function dataProviderPutUnupdatableFields()
    {
        return [
            [ 'author', '/api/doctors/3', [ '@id' => '/api/doctors/1' ] ], // pas modifiable
            [ 'careRequest', '/api/care_requests/2', [ '@id' => '/api/care_requests/1' ] ], // pas modifiable
            [ 'creationDate', '2021-10-01', '2021-09-27T00:00:00+00:00' ], // pas modifiable
            [ 'content', 'updated content', 'updated content'], // modifiable
        ];
    }

    /**
     * @dataProvider dataProviderPutUnupdatableFields
     */
    public function testPutUnupdatableFields($payloadKey, $payloadValue, $expected)
    {
        // On ne peut pas modifier :
        // - la care request
        // - l'auteur
        // - la date de création
        // d'un comment
        
        $this->loginUser('admin@example.com');
        $crawler = $this->client->request('PUT', "/api/comments/1", [
            'json' => [ $payloadKey => $payloadValue ],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([ $payloadKey => $expected ]);
    }
}
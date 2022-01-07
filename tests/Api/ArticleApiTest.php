<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class ArticleApiTest extends AbstractApiTestCase
{
    private const ARTICLE_DATA = [
        'publishFrom' => '2021-12-16 10:00:00',
        'publishTo' => '2021-12-31 10:00:00',
        'style' => 'info',
        'content' => '# Bienvenue\nBienvenue dans *lao*. Le logiciel qui va faire décoler votre liste d’attente.'
    ];

    private const BASE_URL = '/api/articles';

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/article.yaml',
        ]);
    }

    public function dataProviderGetAs()
    {
        return [
            [ 'admin@example.com', Response::HTTP_OK ],
            [ 'user1@example.com', Response::HTTP_FORBIDDEN ],
        ];
    }

    /**
     * @dataProvider dataProviderGetAs
    */
    public function testGetAs($userEmail, $expected)
    {
        $this->loginUser($userEmail);
        $this->client->request('GET', self::BASE_URL . '/1');
        $this->assertResponseStatusCodeSame($expected);
    }


    public function dataProviderPostAs()
    {
        return [
            [ 'admin@example.com', Response::HTTP_CREATED ],
            [ 'user1@example.com', Response::HTTP_FORBIDDEN ],
        ];
    }

    /**
     * @dataProvider dataProviderPostAs
    */
    public function testPostAs($userEmail, $expected)
    {
        $this->loginUser($userEmail);
        $this->client->request('POST', self::BASE_URL, [
            'json' => self::ARTICLE_DATA,
        ]);
        $this->assertResponseStatusCodeSame($expected);

        if ($this->client->getResponse()->getStatusCode() == Response::HTTP_CREATED) {
            // Vérification que l'office est bien créé
            $articleApiId = json_decode($this->client->getResponse()->getContent(), true)['@id'];
            $this->client->request('GET', $articleApiId);
            $this->assertResponseIsSuccessful();
        }
    }


    public function dataProviderDeleteAs()
    {
        return [
            [ 'admin@example.com', Response::HTTP_NO_CONTENT ],
            [ 'user1@example.com', Response::HTTP_FORBIDDEN ],
        ];
    }

    /**
     * @dataProvider dataProviderDeleteAs
     */
    public function testDeleteAs($userEmail, $expected)
    {
        $this->loginUser($userEmail);
        $this->client->request('DELETE', self::BASE_URL . '/2');
        $this->assertResponseStatusCodeSame($expected);
    }


    public function dataProviderPutAs()
    {
        return [
            [ 'admin@example.com', Response::HTTP_OK ],
            [ 'user1@example.com', Response::HTTP_FORBIDDEN ],
        ];
    }

    /**
     * @dataProvider dataProviderPutAs
     */
    public function testPutAs($userEmail, $expected)
    {
        $newContent = 'content modifié';

        $this->loginUser($userEmail);
        $this->client->request('PUT', self::BASE_URL . '/1', [
            'json' => [
                'content' => $newContent,
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);

        if ($this->client->getResponse()->getStatusCode() == Response::HTTP_OK) {
            // Vérification que l'entité est bien modifiée
            $articleApiId = json_decode($this->client->getResponse()->getContent(), true)['@id'];
            $this->client->request('GET', $articleApiId);
            $this->assertResponseIsSuccessful();
            $this->assertJsonContains([ 'content' => $newContent]);
        }
    }


    public function dataProviderPostMissingContent()
    {
        return [
            [ 'publishFrom', Response::HTTP_CREATED, ],
            [ 'publishTo', Response::HTTP_CREATED, ],
            [ 'style', Response::HTTP_CREATED, ],
            [ 'content', Response::HTTP_UNPROCESSABLE_ENTITY, ],
        ];
    }

    /**
     * @dataProvider dataProviderPostMissingContent
     */
    public function testPostMissingContent($content, $expected)
    {
        $this->loginUser('admin@example.com');

        $data = array_diff_key(self::ARTICLE_DATA, [$content => null]);
        $this->client->request('POST', self::BASE_URL, [
            'json' => $data,
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }


    public function dataProviderInconsistentData()
    {
        return [
            [ 'publishFrom', 'not a date', Response::HTTP_BAD_REQUEST, ],
                // je n'arrive pas à correctement tester les dates
            [ 'publishTo', 'not a date', Response::HTTP_BAD_REQUEST, ],
            [ 'style', 'unknown style', Response::HTTP_UNPROCESSABLE_ENTITY, ],
        ];
    }

    /**
     * @dataProvider dataProviderInconsistentData
     */

    public function testInconsistentData($content, $value, $expected)
    {
        $this->loginUser('admin@example.com');

        $data = array_merge(self::ARTICLE_DATA, [$content => $value]);
        $this->client->request('POST', self::BASE_URL, [
            'json' => $data,
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }
}

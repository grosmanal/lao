<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class AbandonReasonApiTest extends AbstractApiTestCase
{
    private const ABANDON_REASON_DATA = [
        'label' => 'label',
    ];

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/abandon_reason.yaml',
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
        $this->client->request('GET', '/api/abandon_reasons/1');
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
        $this->client->request('POST', '/api/abandon_reasons', [
            'json' => self::ABANDON_REASON_DATA,
        ]);
        $this->assertResponseStatusCodeSame($expected);

        if ($this->client->getResponse()->getStatusCode() == Response::HTTP_CREATED) {
            // Vérification que l'office est bien créé
            $abandonReasonApiId = json_decode($this->client->getResponse()->getContent(), true)['@id'];
            $this->client->request('GET', $abandonReasonApiId);
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
        $this->client->request('DELETE', '/api/abandon_reasons/2');
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
        $newLabel = 'label modifié';

        $this->loginUser($userEmail);
        $this->client->request('PUT', '/api/abandon_reasons/1', [
            'json' => [
                'label' => $newLabel,
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);

        if ($this->client->getResponse()->getStatusCode() == Response::HTTP_OK) {
            // Vérification que l'entité est bien modifiée
            $abondonReasonApiId = json_decode($this->client->getResponse()->getContent(), true)['@id'];
            $this->client->request('GET', $abondonReasonApiId);
            $this->assertResponseIsSuccessful();
            $this->assertJsonContains([ 'label' => $newLabel]);
        }
    }


    public function dataProviderPostMissingContent()
    {
        return [
            [ 'label', Response::HTTP_UNPROCESSABLE_ENTITY, ],
        ];
    }

    /**
     * @dataProvider dataProviderPostMissingContent
     */
    public function testPostMissingContent($content, $expected)
    {
        $this->loginUser('admin@example.com');

        $data = array_diff_key(self::ABANDON_REASON_DATA, [$content => null]);
        $this->client->request('POST', "/api/abandon_reasons", [
            'json' => $data,
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }

    public function dataProviderPutDataTooLong()
    {
        return [
            [ 'label', 255, Response::HTTP_OK ],
            [ 'label', 256, Response::HTTP_UNPROCESSABLE_ENTITY ],
        ];
    }

    /**
     * @dataProvider dataProviderPutDataTooLong
     */
    public function testPutDataTooLong($payloadKey, $payloadLength, $expected)
    {
        $this->loginUser('admin@example.com');
        $this->client->request('PUT', '/api/abandon_reasons/1', [
            'json' => [
                $payloadKey => str_repeat('A', $payloadLength),
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }
}

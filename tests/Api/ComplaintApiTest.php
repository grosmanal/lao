<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class ComplaintApiTest extends AbstractApiTestCase
{
    private const COMPLAINT_DATA = [
        'label' => 'label',
    ];

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/complaint.yaml',
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
        $this->client->request('GET', '/api/complaints/1');
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
        $this->client->request('POST', '/api/complaints', [
            'json' => self::COMPLAINT_DATA,
        ]);
        $this->assertResponseStatusCodeSame($expected);

        if ($this->client->getResponse()->getStatusCode() == Response::HTTP_CREATED) {
            // Vérification que l'office est bien créé
            $complaintApiId = json_decode($this->client->getResponse()->getContent(), true)['@id'];
            $this->client->request('GET', $complaintApiId);
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
        $this->client->request('DELETE', '/api/complaints/2');
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
        $newComplaintLabel = 'label modifié';

        $this->loginUser($userEmail);
        $this->client->request('PUT', '/api/complaints/1', [
            'json' => [
                'label' => $newComplaintLabel,
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);

        if ($this->client->getResponse()->getStatusCode() == Response::HTTP_OK) {
            // Vérification que la complaint est bien modifiée
            $complaintApiId = json_decode($this->client->getResponse()->getContent(), true)['@id'];
            $this->client->request('GET', $complaintApiId);
            $this->assertResponseIsSuccessful();
            $this->assertJsonContains([ 'label' => $newComplaintLabel]);
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

        $data = array_diff_key(self::COMPLAINT_DATA, [$content => null]);
        $this->client->request('POST', "/api/complaints", [
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
        $this->client->request('PUT', '/api/complaints/1', [
            'json' => [
                $payloadKey => str_repeat('A', $payloadLength),
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }
}

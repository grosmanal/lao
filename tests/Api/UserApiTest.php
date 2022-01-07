<?php

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class UserApiTest extends AbstractApiTestCase
{
    private const BASE_URL = '/api/users';

    private const DATA = [
        'email' => 'user_test@example.com',
        'roles' => ['ROLE_ADMIN'],
        'password' => 'not_realy_hashed_password',
        'firstname' => 'firstname_test',
        'lastname' => 'lastname_test',
        'avatarName' => '123456789.png',
    ];

    public function setUp(): void
    {
        $this->setUpTestController([
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

    public function testGet()
    {
        $this->loginUser('admin@example.com');
        $this->client->request('GET', self::BASE_URL . '/5');
        $this->assertJsonContains([
            'email' => 'user3@example.com',
            'roles' => ['ROLE_PATIENT'],
            'password' => '$2y$13$N1dxqPx7LdFWDvwrZAA1Q.deK2FjoxzkhNHzOOeJTbsuDvMY3GU36',
            'firstname' => 'user_firstname',
            'lastname' => 'user_lastname',
        ]);
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
            'json' => self::DATA,
        ]);
        $this->assertResponseStatusCodeSame($expected);

        if ($this->client->getResponse()->getStatusCode() == Response::HTTP_CREATED) {
            // Vérification que l'entité est bien créée
            $entityUri = json_decode($this->client->getResponse()->getContent(), true)['@id'];
            $this->client->request('GET', $entityUri);
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
        $this->client->request('DELETE', self::BASE_URL . '/5');
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
                'firstname' => $newContent,
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);

        if ($this->client->getResponse()->getStatusCode() == Response::HTTP_OK) {
            // Vérification que l'entité est bien modifiée
            $entityUri = json_decode($this->client->getResponse()->getContent(), true)['@id'];
            $this->client->request('GET', $entityUri);
            $this->assertResponseIsSuccessful();
            $this->assertJsonContains([ 'firstname' => $newContent]);
        }
    }

    public function dataProviderPostMissingContent()
    {
        return [
            [ 'email', Response::HTTP_UNPROCESSABLE_ENTITY, ],
            [ 'roles', Response::HTTP_UNPROCESSABLE_ENTITY, ],
            [ 'password', Response::HTTP_UNPROCESSABLE_ENTITY, ],
            [ 'firstname', Response::HTTP_UNPROCESSABLE_ENTITY, ],
            [ 'lastname', Response::HTTP_UNPROCESSABLE_ENTITY, ],
            [ 'avatarName', Response::HTTP_CREATED, ],
        ];
    }

    /**
     * @dataProvider dataProviderPostMissingContent
     */
    public function testPostMissingContent($content, $expected)
    {
        $this->loginUser('admin@example.com');

        $data = array_diff_key(self::DATA, [$content => null]);
        $this->client->request('POST', self::BASE_URL, [
            'json' => $data,
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }

    public function dataProviderInconsistentData()
    {
        return [
            [ 'email', 'not a email', Response::HTTP_UNPROCESSABLE_ENTITY, ],
            [ 'roles', 'not a array', Response::HTTP_BAD_REQUEST, ],
        ];
    }

    /**
     * @dataProvider dataProviderInconsistentData
     */

    public function testInconsistentData($content, $value, $expected)
    {
        $this->loginUser('admin@example.com');

        $data = array_merge(self::DATA, [$content => $value]);
        $this->client->request('POST', self::BASE_URL, [
            'json' => $data,
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }

    public function dataProviderPutDataTooLong()
    {
        return [
            [ 'firstname', 255, Response::HTTP_OK ],
            [ 'firstname', 256, Response::HTTP_UNPROCESSABLE_ENTITY ],
            [ 'lastname', 255, Response::HTTP_OK ],
            [ 'lastname', 256, Response::HTTP_UNPROCESSABLE_ENTITY ],
            [ 'avatarName', 255, Response::HTTP_OK ],
            [ 'avatarName', 256, Response::HTTP_UNPROCESSABLE_ENTITY ],
        ];
    }

    /**
     * @dataProvider dataProviderPutDataTooLong
     */
    public function testPutDataTooLong($payloadKey, $payloadLength, $expected)
    {
        $this->loginUser('admin@example.com');
        $this->client->request('PUT', self::BASE_URL . '/1', [
            'json' => [
                $payloadKey => str_repeat('A', $payloadLength),
            ],
        ]);
        $this->assertResponseStatusCodeSame($expected);
    }
}

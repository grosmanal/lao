<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

class CommentControllerTest extends AbstractControllerTestCase
{
    public function setUp(): void
    {
        $this->setUpTestController([__DIR__ . '/../../fixtures/tests/comment.yaml']);
    }


    public function dataProviderGet()
    {
        return [
            [1, Response::HTTP_OK],
            [99, Response::HTTP_NOT_FOUND],
        ];
    }

    /**
     * @dataProvider dataProviderGet
     */
    public function testGet($commentId, $expected): void
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', "/comments/${commentId}");

        $this->assertResponseStatusCodeSame($expected);
    }

    public function testContent()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', "/comments/1");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('article p', 'lorem ipsum comment_1');
    }

    public function testGetAsAnonymous(): void
    {
        $crawler = $this->client->request('GET', "/comments/1");
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND); // Redirection vers le login
        $this->assertResponseRedirects('/login');
    }


    public function dataProviderGetAsDoctor()
    {
        return [
            ['user1@example.com', Response::HTTP_OK ], // Auteur du commentaire
            ['user2@example.com', Response::HTTP_FORBIDDEN ], // Même cabinet
            ['user3@example.com', Response::HTTP_FORBIDDEN ], // Autre cabinet
        ];
    }

    /**
     * @dataProvider dataProviderGetAsDoctor
     */
    public function testGetAsDoctor($userEmail, $expected): void
    {
        $this->loginUser($userEmail);
        $crawler = $this->client->request('GET', "/comments/1");

        $this->assertResponseStatusCodeSame($expected);
    }

    public function testForm()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', sprintf('/comment_forms/1'));
        $this->assertSelectorExists('textarea', 'lorem ipsum comment_1');
    }


    public function dataProviderCommentFormWrongCareRequest()
    {
        return [
            [ 1, Response::HTTP_OK ],
            [ 3, Response::HTTP_FORBIDDEN ],
            [ 99, Response::HTTP_NOT_FOUND],
        ];
    }

    /**
     * @dataProvider dataProviderCommentFormWrongCareRequest
     */
    public function testCommentFormWrongCareRequest($commentId, $expected)
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', sprintf('/comment_forms/%d', $commentId));
        $this->assertResponseStatusCodeSame($expected);
    }
}

<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use App\Repository\ArticleRepository;
use App\Repository\DoctorRepository;

class ArticleControllerTest extends AbstractControllerTestCase
{
    private ArticleRepository $articleRepository;

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/article.yaml',
        ]);
        
        $container = static::getContainer();
        $this->articleRepository = $container->get(ArticleRepository::class);
    }
    
    public function testPostAsAnonymous()
    {
        $crawler = $this->client->request('POST', "/article_mark_read/1");
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND); // Redirection vers le login
        $this->assertResponseRedirects('/login');
    }

    public function testPostAsAdmin()
    {
        $this->loginUser('admin@example.com');
        $crawler = $this->client->request('POST', "/article_mark_read/1");
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    
    public function testNonExistantArticle()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('POST', "/article_mark_read/5");
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testPostAsDoctor()
    {
        $email = 'user1@example.com';
        $this->loginUser($email);

        // L'article 2 a déjà été lu
        $crawler = $this->client->request('POST', "/article_mark_read/2");
        $this->assertResponseIsSuccessful();
        $this->assertEquals(1, $this->getArticlesCountReadByDoctor($email));

        // L'article 1 n'a pas été lu
        $crawler = $this->client->request('POST', "/article_mark_read/1");
        $this->assertResponseIsSuccessful();
        $this->assertEquals(2, $this->getArticlesCountReadByDoctor($email));
    }
    
    private function getArticlesCountReadByDoctor(string $email)
    {
        $doctor = $this->getUserAsDoctor($email);
        return count($this->articleRepository->findReadByDoctor($doctor));
    }
}
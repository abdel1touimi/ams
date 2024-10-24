<?php

namespace App\Tests\Functional\Controller;

use App\Document\Article;
use App\Tests\Functional\TestTrait\FunctionalTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleControllerTest extends WebTestCase
{
    use FunctionalTestTrait;

    private array $headers = [];
    private const API_ENDPOINT = '/api/articles';

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->documentManager = static::getContainer()->get('doctrine_mongodb.odm.document_manager');
        $this->clearDatabase();

        // Create a user and get authentication headers
        $this->createUser();
        $this->headers = array_merge(
            $this->getAuthorizationHeader(),
            ['CONTENT_TYPE' => 'application/json']
        );
    }

    public function testCreateArticle(): void
    {
        $articleData = [
            'title' => 'Test Article',
            'content' => 'This is a test article content with sufficient length'
        ];

        $this->client->request(
            'POST',
            self::API_ENDPOINT,
            [],
            [],
            $this->headers,
            json_encode($articleData)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertTrue($content['success']);
        self::assertArrayHasKey('data', $content);
        self::assertEquals($articleData['title'], $content['data']['title']);
        self::assertEquals($articleData['content'], $content['data']['content']);
        self::assertArrayHasKey('id', $content['data']);
    }

    public function testCreateArticleWithInvalidData(): void
    {
        $invalidData = [
            'title' => '', // Empty title
            'content' => 'short' // Content too short
        ];

        $this->client->request(
            'POST',
            self::API_ENDPOINT,
            [],
            [],
            $this->headers,
            json_encode($invalidData)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertFalse($content['success']);
        self::assertArrayHasKey('data', $content);

        $errors = $content['data'];
        self::assertArrayHasKey('title', $errors);
        self::assertArrayHasKey('content', $errors);
    }

    public function testListArticles(): void
    {
        // Create some test articles
        $this->createTestArticles();

        $this->client->request(
            'GET',
            self::API_ENDPOINT,
            [],
            [],
            $this->headers
        );

        self::assertResponseIsSuccessful();

        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertTrue($content['success']);
        self::assertArrayHasKey('data', $content);
        self::assertIsArray($content['data']);
        self::assertCount(2, $content['data']); // We created 2 articles
    }

    public function testUpdateArticle(): void
    {
        $article = $this->createTestArticles()[0];

        $updateData = [
            'title' => 'Updated Title',
            'content' => 'This is the updated content with sufficient length'
        ];

        $this->client->request(
            'PUT',
            self::API_ENDPOINT . '/' . $article->getId(),
            [],
            [],
            $this->headers,
            json_encode($updateData)
        );

        self::assertResponseIsSuccessful();

        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertTrue($content['success']);
        self::assertArrayHasKey('data', $content);
        self::assertEquals($updateData['title'], $content['data']['title']);
        self::assertEquals($updateData['content'], $content['data']['content']);
    }

    public function testUpdateNonExistentArticle(): void
    {
        $updateData = [
            'title' => 'Updated Title',
            'content' => 'This is the updated content'
        ];

        $this->client->request(
            'PUT',
            self::API_ENDPOINT . '/nonexistent_id',
            [],
            [],
            $this->headers,
            json_encode($updateData)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteArticle(): void
    {
        $article = $this->createTestArticles()[0];

        $this->client->request(
            'DELETE',
            self::API_ENDPOINT . '/' . $article->getId(),
            [],
            [],
            $this->headers
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Verify article is deleted
        $this->client->request(
            'GET',
            self::API_ENDPOINT . '/' . $article->getId(),
            [],
            [],
            $this->headers
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteNonExistentArticle(): void
    {
        $this->client->request(
            'DELETE',
            self::API_ENDPOINT . '/nonexistent_id',
            [],
            [],
            $this->headers
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUnauthorizedAccess(): void
    {
        // Try to access without authentication
        $this->client->request('GET', self::API_ENDPOINT);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        // Try to create without authentication
        $this->client->request(
            'POST',
            self::API_ENDPOINT,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Test', 'content' => 'Content'])
        );
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Creates test articles and returns them
     * @return Article[]
     */
    private function createTestArticles(): array
    {
        $articles = [];

        for ($i = 1; $i <= 2; $i++) {
            $article = new Article();
            $article->setTitle("Test Article $i");
            $article->setContent("This is test content for article $i");
            $article->setAuthorId($this->getUserIdFromToken());

            $this->documentManager->persist($article);
            $articles[] = $article;
        }

        $this->documentManager->flush();
        return $articles;
    }

    private function getUserIdFromToken(): string
    {
        $user = $this->documentManager->getRepository('App\Document\User')
            ->findOneBy(['email' => 'test@example.com']);
        return $user->getId();
    }
}

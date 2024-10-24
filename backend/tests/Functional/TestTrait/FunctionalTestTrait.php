<?php

namespace App\Tests\Functional\TestTrait;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait FunctionalTestTrait
{
    protected ?DocumentManager $documentManager = null;
    protected ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->documentManager = static::getContainer()->get(DocumentManager::class);
        $this->clearDatabase();
    }

    protected function tearDown(): void
    {
        $this->clearDatabase();
        $this->documentManager = null;
        $this->client = null;
        parent::tearDown();
    }

    protected function clearDatabase(): void
    {
        if ($this->documentManager) {
            $collections = $this->documentManager->getClient()
                ->selectDatabase($this->documentManager->getConfiguration()->getDefaultDB())
                ->listCollections();

            foreach ($collections as $collection) {
                $this->documentManager->getClient()
                    ->selectDatabase($this->documentManager->getConfiguration()->getDefaultDB())
                    ->selectCollection($collection->getName())
                    ->drop();
            }

            $this->documentManager->clear();
        }
    }

    protected function createUser(string $email = 'test@example.com', string $password = 'Password123'): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setName('Test User');

        $hasher = static::getContainer()->get('security.user_password_hasher');
        $hashedPassword = $hasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->documentManager->persist($user);
        $this->documentManager->flush();

        return $user;
    }

    protected function getAuthorizationHeader(string $email = 'test@example.com', string $password = 'Password123'): array
    {
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $email,
                'password' => $password
            ])
        );

        self::assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('token', $data);

        return ['HTTP_AUTHORIZATION' => 'Bearer ' . $data['token']];
    }
}

<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ODM\MongoDB\DocumentManager;

abstract class DatabaseTestCase extends WebTestCase
{
    protected ?DocumentManager $documentManager = null;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $this->documentManager = static::getContainer()->get(DocumentManager::class);
        $this->clearDatabase();
    }

    protected function tearDown(): void
    {
        $this->clearDatabase();
        $this->documentManager = null;
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
}

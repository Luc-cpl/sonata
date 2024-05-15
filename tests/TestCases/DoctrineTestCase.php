<?php

namespace Tests\TestCases;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Orkestra\Testing\AbstractTestCase;
use Sonata\Providers\DoctrineProvider;
use Tests\Entities\DoctrineUser;

abstract class DoctrineTestCase extends AbstractTestCase
{
    /**
     * Setup the test environment.
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        app()->provider(DoctrineProvider::class);
        app()
            ->config()
            ->set('sonata.user_entity', DoctrineUser::class)
            ->set('doctrine.entities', fn () => [app()->config()->get('root') . '/tests/Entities'])
            ->set('doctrine.connection', fn () => [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ]);
    }

    /**
     * Performs assertions shared by all tests of a test case.
     *
     * This method is called between setUp() and test.
     */
    protected function assertPreConditions(): void
    {
        parent::assertPreConditions();

        $entityManager = app()->get(EntityManagerInterface::class);

        $schemaTool = app()->get(SchemaTool::class);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}

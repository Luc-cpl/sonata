<?php

namespace Sonata\Testing;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sonata\Doctrine\DoctrineProvider;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class Doctrine
{
    /**
     * Initialize the Doctrine provider for testing.
     * It will use an in-memory SQLite database and create the schema from entities.
     * This will not use existing migrations.
     */
    public static function init(): void
    {
        app()->provider(DoctrineProvider::class);
        app()->config()->set('doctrine.connection', fn () => [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        // Generate the schema directly from entities
        app()->decorate(EntityManagerInterface::class, function (EntityManagerInterface $entityManager) {
            $schemaTool = new SchemaTool($entityManager);
            $metadata   = $entityManager->getMetadataFactory()->getAllMetadata();
            $schemaTool->createSchema($metadata);
            return $entityManager;
        });
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T|null
     */
    public static function find(string $className, mixed $id, null|LockMode|int $lockMode = null, ?int $lockVersion = null): ?object
    {
        $manager = app()->get(EntityManagerInterface::class);

        /**
         * Ensure different instances
         * are returned during testing.
         */
        $manager->clear();

        return $manager->find($className, $id, $lockMode, $lockVersion);
    }

    /**
     * Flush the entity manager.
     */
    public static function flush(): void
    {
        app()->get(EntityManagerInterface::class)->flush();
    }

    /**
     * Factory method to create entities.
     * Useful for creating entities in tests.
     *
     * @template T of object
     * @param class-string<T> $className
     * @param mixed[]|callable $args
     * @return Collection<T>
     */
    public static function factory(string $className, int $number = 1, array|callable $args = []): Collection
    {
        $args = is_callable($args) ? [$args] : $args;
        $manager = app()->get(EntityManagerInterface::class);
        $factory = factory();
        $entities = [];

        for ($i = 0; $i < $number; $i++) {
            $entity = $factory->make($className, ...$args);
            $manager->persist($entity);
            $entities[] = $entity;
        }

        $manager->flush();
        return new ArrayCollection($entities);
    }

    /**
     * Remove an entity from the database.
     */
    public static function remove(object $entity): void
    {
        $manager = app()->get(EntityManagerInterface::class);
        $manager->remove($entity);
        $manager->flush();
    }
}

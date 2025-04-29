<?php

namespace Sonata\Doctrine;

use Orkestra\Interfaces\ConfigurationInterface;

/**
 * Class EntityRegistry
 *
 * This class is used to register entities to be used by the Doctrine ORM.
 * It allows easy registration of entities from different providers.
 */
class EntityRegistry
{
    /**
     * @var string[]
     */
    private static array $entities = [];

    public function __construct(
        private ConfigurationInterface $config
    ) {
        //
    }

    /**
     * Retrieves the list of registered entities.
     *
     * @return string[]|class-string[]
     */
    public function getEntities(): array
    {
        /** @var string */
        $root = $this->config->get('root');
        $root = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return array_map(
            fn (string $entity): string => class_exists($entity)
                ? $entity
                : (is_dir($entity) || is_file($entity) ? $entity : $root . $entity),
            self::$entities
        );
    }

    /**
     * Registers an entity to be used by the Doctrine ORM.
     *
     * @param string|class-string $entity The entity class name or directory to entities
     */
    public static function register(string $entity): void
    {
        if (in_array($entity, self::$entities, true)) {
            return;
        }
        self::$entities[] = $entity;
    }
}

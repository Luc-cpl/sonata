<?php

namespace Sonata\Doctrine;

use Orkestra\App;
use Doctrine\ORM\Mapping\EntityListenerResolver as EntityListenerResolverInterface;

/**
 * Class EntityListenerResolver
 *
 * Allow resolution of entity listener classes from a PSR container.
 */
class EntityListenerResolver implements EntityListenerResolverInterface
{
    /** @var array<class-string, object> Map to store entity listener instances. */
    private array $instances = [];

    public function __construct(
        private App $app
    ) {
        //
    }

    public function clear(string|null $className = null): void
    {
        if ($className === null) {
            $this->instances = [];

            return;
        }

        $className = trim($className, '\\');
        unset($this->instances[$className]);
    }

    public function register(object $object): void
    {
        $this->instances[$object::class] = $object;
    }

    public function resolve(string $className): object
    {
		/** @var class-string */
        $className = trim($className, '\\');
        return $this->instances[$className] ??= $this->app->get($className);
    }
}

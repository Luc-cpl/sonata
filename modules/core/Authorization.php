<?php

namespace Sonata;

use Orkestra\Interfaces\ConfigurationInterface;
use Sonata\Interfaces\AuthDriverInterface;
use Sonata\Interfaces\Repository\IdentifiableInterface;
use Orkestra\Interfaces\AppContainerInterface;
use Sonata\Interfaces\AuthInterface;
use InvalidArgumentException;

/**
 * @template T of object
 * @implements AuthInterface<T>
 */
class Authorization implements AuthInterface
{
    /**
     * @var AuthDriverInterface<T>[]
     */
    private array $instances = [];

    private string $defaultGuard;

    private string $currentGuard;

    public function __construct(
        private ConfigurationInterface $config,
        private AppContainerInterface $app
    ) {
        /** @var string */
        $defaultGuard = $this->config->get('sonata.default_guard');
        $this->defaultGuard = $defaultGuard;
        $this->currentGuard = $defaultGuard;
    }

    /**
     * Retrieves the guard instance for the given guard key.
     * Usually this method should be called by a middleware to retrieve the
     * guard instance for the current request.
     *
     * @return AuthDriverInterface<T>
     */
    public function guard(?string $guard = null): AuthDriverInterface
    {
        $guard ??= $this->defaultGuard;
        $this->currentGuard = $guard;

        if (array_key_exists($guard, $this->instances)) {
            return $this->instances[$guard];
        }

        /** @var array<string, array{driver: class-string, repository: class-string}> */
        $guards = $this->config->get('sonata.auth_guards');
        if (!array_key_exists($guard, $guards)) {
            throw new InvalidArgumentException("The guard \"$guard\" does not exist");
        }

        $guardKey = $guard;
        $guard = $guards[$guard];

        /** @var AuthDriverInterface<T> */
        $driver = $this->app->make($guard['driver']);

        /** @var IdentifiableInterface<T> */
        $repository = $this->app->get($guard['repository']);

        $driver->setRepository($repository);
        $driver->setGuard($guardKey);

        $this->instances[$guardKey] = $driver;

        return $driver;
    }

    public function subject(): ?object
    {
        return $this->guard($this->currentGuard)->subject();
    }

    public function check(): bool
    {
        return $this->guard($this->currentGuard)->check();
    }

    public function authenticate(object $subject)
    {
        $this->guard($this->currentGuard)->authenticate($subject);
    }

    public function revoke(): void
    {
        $this->guard($this->currentGuard)->revoke();
    }
}

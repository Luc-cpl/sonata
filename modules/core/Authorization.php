<?php

namespace Sonata;

use Orkestra\Interfaces\ConfigurationInterface;
use Sonata\Interfaces\AuthDriverInterface;
use Sonata\Interfaces\RepositoryInterface;
use Orkestra\Interfaces\AppContainerInterface;
use Sonata\Interfaces\AuthInterface;
use InvalidArgumentException;

class Authorization implements AuthInterface
{
    /**
     * @var AuthDriverInterface[]
     */
    private array $instances = [];

    private string $defaultGuard;

    private string $currentGuard;

    public function __construct(
        private ConfigurationInterface $config,
        private AppContainerInterface $app
    ) {
        $this->defaultGuard = $this->config->get('sonata.default_guard');
        $this->currentGuard = $this->defaultGuard;
    }

    /**
     * Retrieves the guard instance for the given guard key.
     * Usually this method should be called by a middleware to retrieve the
     * guard instance for the current request.
     */
    public function guard(?string $guard = null): AuthDriverInterface
    {
        $guard ??= $this->defaultGuard;
        $this->currentGuard = $guard;

        if (array_key_exists($guard, $this->instances)) {
            return $this->instances[$guard];
        }

        $guards = $this->config->get('sonata.auth_guards');
        if (!array_key_exists($guard, $guards)) {
            throw new InvalidArgumentException("The guard \"$guard\" does not exist");
        }

        $guardKey   = $guard;
        $guard      = $guards[$guard];
        $driver     = $guard['driver'];
        $repository = $guard['repository'];

        /** @var AuthDriverInterface */
        $driver = $this->app->make($driver);

        /** @var RepositoryInterface */
        $repository = $this->app->get($repository);

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

    public function authenticate(object $subject): void
    {
        $this->guard($this->currentGuard)->authenticate($subject);
    }

    public function revoke(): void
    {
        $this->guard($this->currentGuard)->revoke();
    }
}

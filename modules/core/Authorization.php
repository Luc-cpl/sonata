<?php

namespace Sonata;

use Orkestra\Interfaces\ConfigurationInterface;
use Sonata\Interfaces\AuthGuardInterface;
use Sonata\Interfaces\Repository\IdentifiableInterface;
use Orkestra\Interfaces\AppContainerInterface;
use Sonata\Interfaces\AuthInterface;
use Sonata\Interfaces\SessionInterface;
use InvalidArgumentException;

/**
 * @template T of object
 * @implements AuthInterface<T>
 */
class Authorization implements AuthInterface
{
    /**
     * @var AuthGuardInterface<T>[]
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
     * @return AuthGuardInterface<T>
     */
    public function guard(?string $guard = null): AuthGuardInterface
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
        $guardParams = $guards[$guard];

        /** @var AuthGuardInterface<T> */
        $guard = $this->app->make(AuthGuardInterface::class);

        /** @var SessionInterface */
        $driver = $this->app->get($guardParams['driver']);
        $driver = $driver->guardedBy($guardKey);

        $guard->setDriver($driver);

        /** @var IdentifiableInterface<T> */
        $repository = $this->app->get($guardParams['repository']);

        if (!$guard->session()->started()) {
            $guard->session()->start();
        }

        $guard->setRepository($repository);
        $guard->setName($guardKey);

        $this->instances[$guardKey] = $guard;

        return $guard;
    }

    public function user(): ?object
    {
        return $this->guard($this->currentGuard)->user();
    }

    public function check(): bool
    {
        return $this->guard($this->currentGuard)->check();
    }

    public function authenticate(object $user)
    {
        return $this->guard($this->currentGuard)->authenticate($user);
    }

    public function revoke(): void
    {
        $this->guard($this->currentGuard)->revoke();
    }

    public function session(): SessionInterface
    {
        return $this->guard($this->currentGuard)->session();
    }
}

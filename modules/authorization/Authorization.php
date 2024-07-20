<?php

namespace Sonata\Authorization;

use Orkestra\Interfaces\ConfigurationInterface;
use Orkestra\Interfaces\AppContainerInterface;
use Sonata\Repositories\Interfaces\Partials\IdentifiableRepositoryInterface;
use Sonata\Authorization\Interfaces\AuthGuardInterface;
use Sonata\Authorization\Interfaces\AuthInterface;
use Sonata\Sessions\Interfaces\SessionInterface;
use Sonata\Entities\Interfaces\IdentifiableInterface;
use InvalidArgumentException;

/**
 * @template T of IdentifiableInterface
 * @implements AuthInterface<T>
 */
class Authorization implements AuthInterface
{
    /**
     * @var AuthGuardInterface<T>[]
     */
    private array $instances = [];

    /**
     * @var array<string, array{driver: string, repository: class-string}>
     */
    private array $guards;

    private string $defaultGuard;

    private ?string $currentGuard = null;

    public function __construct(
        private ConfigurationInterface $config,
        private AppContainerInterface $app
    ) {
        /** @var string */
        $defaultGuard = $this->config->get('sonata.default_guard');
        /** @var array<string, array{driver: string, repository: class-string}> */
        $guards = $this->config->get('sonata.auth_guards');
        $this->guards = $guards;
        $this->defaultGuard = $defaultGuard;
    }

    public function getActiveGuardName(): ?string
    {
        return $this->currentGuard;
    }

    /**
     * Retrieves the options for the given guard.
     *
     * @return array{driver: string, repository: class-string}|array{}
     */
    public function getGuardOptions(string $guard): array
    {
        return $this->guards[$guard] ?? [];
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

        $guardParams = $this->getGuardOptions($guard);

        if (empty($guardParams)) {
            throw new InvalidArgumentException("The guard \"$guard\" does not exist");
        }

        if (array_key_exists($guard, $this->instances)) {
            return $this->instances[$guard];
        }

        try {
            /** @var IdentifiableRepositoryInterface<T> */
            $repository = $this->app->get($guardParams['repository']);
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Failed to retrieve repository for guard \"$guard\": " . $e->getMessage());
        }

        try {
            /** @var AuthGuardInterface<T> */
            $instance = $this->app->make(AuthGuardInterface::class, [
                'repository' => $repository,
                'driver' => $guardParams['driver'],
                'name' => $guard,
            ]);
            $this->instances[$guard] = $instance;
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Failed to create guard \"$guard\": " . $e->getMessage());
        }

        return $this->instances[$guard];
    }

    public function user(): ?object
    {
        return $this->guard($this->currentGuard)->user();
    }

    public function authenticate(IdentifiableInterface $user)
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

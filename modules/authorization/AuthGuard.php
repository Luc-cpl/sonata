<?php

namespace Sonata\Authorization;

use Sonata\Authorization\Interfaces\AuthGuardInterface;
use Sonata\Entities\Interfaces\IdentifiableInterface;
use Sonata\Repositories\Interfaces\Partials\IdentifiableRepositoryInterface;
use Sonata\Sessions\Interfaces\SessionInterface;
use Sonata\Sessions\SessionDrivers;

/**
 * @template T of IdentifiableInterface
 * @implements AuthGuardInterface<T>
 */
class AuthGuard implements AuthGuardInterface
{
    /**
     * @var T|null
     */
    private ?object $user = null;

    /**
     * @param IdentifiableRepositoryInterface<T> $repository
     */
    public function __construct(
        private IdentifiableRepositoryInterface $repository,
        private SessionInterface $session,
        private SessionDrivers $drivers,
        private string $driver,
        private string $name,
    ) {
        //
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param T $user
     */
    public function authenticate(IdentifiableInterface $user): void
    {
        $this->user = $user;
        $this->session()->set('user_id', $user->getId());
    }

    public function user(): ?object
    {
        if ($this->user) {
            return $this->user;
        }

        /** @var int|string|null */
        $userId = $this->session()->get('user_id');
        if (!$userId) {
            return null;
        }

        /** @var T|null */
        $user = $this->repository->get($userId);
        $this->user ??= $user;
        return $this->user;
    }

    public function revoke(): void
    {
        $this->user = null;
        $this->session()->remove('user_id');
    }

    public function session(): SessionInterface
    {
        // Ensures the correct driver is used
        $this->drivers->use($this->driver);
        return $this->session;
    }
}

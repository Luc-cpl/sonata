<?php

namespace Sonata;

use Sonata\Interfaces\AuthGuardInterface;
use Sonata\Interfaces\Repository\IdentifiableInterface;
use Sonata\Interfaces\SessionInterface;

/**
 * @template T of object
 * @implements AuthGuardInterface<T>
 */
class AuthGuard implements AuthGuardInterface
{
    /**
     * @var IdentifiableInterface<T>
     */
    protected IdentifiableInterface $repository;

    protected string $name;

    protected SessionInterface $session;

    /**
     * @var T|null
     */
    protected ?object $user = null;

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setDriver(SessionInterface $session): void
    {
        $this->session = $session;
    }

    /**
     * @param IdentifiableInterface<T> $repository
     */
    public function setRepository(IdentifiableInterface $repository): void
    {
        $this->repository = $repository;
    }

    public function authenticate(object $user): void
    {
        $this->user = $user;
        // @phpstan-ignore-next-line
        $this->session->setUserId($user->id);
    }

    public function check(): bool
    {
        /**
         * As in most cases we will use the user() method in the request cycle,
         * we can just check if the user() method returns null or not instead of
         * checking the database for the user existence as only checking the session
         * not ensures that the user is still valid.
         */
        return $this->user() !== null;
    }

    public function user(): ?object
    {
        $userId = $this->session->getUserId();
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
        $this->session->removeUserId();
    }

    public function session(): SessionInterface
    {
        return $this->session;
    }
}

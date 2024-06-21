<?php

namespace Sonata\AuthDrivers;

use Sonata\Interfaces\AuthDriverInterface;
use Sonata\Interfaces\Repository\IdentifiableInterface;
use Sonata\Interfaces\SessionInterface;

/**
 * @template T of object
 * @implements AuthDriverInterface<T>
 */
class SessionDriver implements AuthDriverInterface
{
    /**
     * @var IdentifiableInterface<T>
     */
    protected IdentifiableInterface $repository;

    protected string $guard;

    /**
     * @var T|null
     */
    protected ?object $subject = null;

    public function __construct(
        protected SessionInterface $session
    ) {
        //
    }

    public function setGuard(string $guard): void
    {
        $this->guard = $guard;
    }

    /**
     * @param IdentifiableInterface<T> $repository
     */
    public function setRepository(IdentifiableInterface $repository): void
    {
        $this->repository = $repository;
    }

    public function authenticate(object $subject): void
    {
        /** @var string */
        $guardKey = $this->guardKey();
        $this->subject = $subject;
        // @phpstan-ignore-next-line
        $this->session->set($guardKey, $subject->id);
    }

    public function check(): bool
    {
        /**
         * As in most cases we will use the subject() method in the request cycle,
         * we can just check if the subject() method returns null or not instead of
         * checking the database for the subject existence as only checking the session
         * not ensures that the subject is still valid.
         */
        return $this->subject() !== null;
    }

    public function subject(): ?object
    {
        if (!$this->session->has($this->guardKey())) {
            return null;
        }
        /** @var string */
        $guardKey = $this->session->get($this->guardKey());

        /** @var T|null */
        $subject = $this->repository->get($guardKey);
        $this->subject ??= $subject;
        return $this->subject;
    }

    public function revoke(): void
    {
        $this->session->remove($this->guardKey());
    }

    protected function guardKey(): string
    {
        return "guards.{$this->guard}.subject";
    }
}

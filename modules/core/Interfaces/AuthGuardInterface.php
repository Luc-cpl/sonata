<?php

namespace Sonata\Interfaces;

use Sonata\Interfaces\Repository\IdentifiableInterface;

/**
 * @template T of object
 * @extends AuthInterface<T>
 */
interface AuthGuardInterface extends AuthInterface
{
    /**
     * Sets the guard key for the driver.
     */
    public function setName(string $guard): void;

    /**
     * Sets the driver to be used by the guard.
     */
    public function setDriver(SessionInterface $session): void;

    /**
     * Sets the repository to be used by the driver.
     *
     * @param IdentifiableInterface<T> $repository
     */
    public function setRepository(IdentifiableInterface $repository): void;
}

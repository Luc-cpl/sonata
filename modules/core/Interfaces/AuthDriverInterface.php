<?php

namespace Sonata\Interfaces;

use Sonata\Interfaces\Repository\IdentifiableInterface;

/**
 * @template T of object
 * @extends AuthInterface<T>
 */
interface AuthDriverInterface extends AuthInterface
{
    /**
     * Sets the guard key for the driver.
     */
    public function setGuard(string $guard): void;

    /**
     * Sets the repository to be used by the driver.
     *
     * @param IdentifiableInterface<T> $repository
     */
    public function setRepository(IdentifiableInterface $repository): void;
}

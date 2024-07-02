<?php

namespace Sonata\Authorization\Interfaces;

/**
 * @template T of object
 * @extends AuthInterface<T>
 */
interface AuthGuardInterface extends AuthInterface
{
    /**
     * Retrieves the guard name.
     */
    public function getName(): string;
}

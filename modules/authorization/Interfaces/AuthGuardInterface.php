<?php

namespace Sonata\Authorization\Interfaces;

use Sonata\Entities\Interfaces\IdentifiableInterface;

/**
 * @template T of IdentifiableInterface
 * @extends AuthInterface<T>
 */
interface AuthGuardInterface extends AuthInterface
{
    /**
     * Retrieves the guard name.
     */
    public function getName(): string;
}

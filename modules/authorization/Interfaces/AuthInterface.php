<?php

namespace Sonata\Authorization\Interfaces;

use Sonata\Entities\Interfaces\IdentifiableInterface;
use Sonata\Sessions\Interfaces\SessionInterface;

/**
 * @template T of IdentifiableInterface
 */
interface AuthInterface
{
    /**
     * Performs the authentication process for the given user.
     * Depending on the driver it may return some data as a result
     * to be retrieved in the request cycle.
     *
     * @param T $user
     * @return mixed[]|string|void
     */
    public function authenticate(IdentifiableInterface $user);

    /**
     * Retrieves the authenticated user.
     * @return T|null
     */
    public function user(): ?object;

    /**
     * Revokes the user authentication.
     */
    public function revoke(): void;

    /**
     * Retrieves the session instance.
     */
    public function session(): SessionInterface;
}

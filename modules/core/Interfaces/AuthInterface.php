<?php

namespace Sonata\Interfaces;

/**
 * @template T of object
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
    public function authenticate(object $user);

    /**
     * Checks if the user is authenticated.
     */
    public function check(): bool;

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
     * Retrieves the used session instance.
     */
    public function session(): SessionInterface;
}

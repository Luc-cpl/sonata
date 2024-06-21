<?php

namespace Sonata\Interfaces;

/**
 * @template T of object
 */
interface AuthInterface
{
    /**
     * Performs the authentication process for the given subject.
     * Depending on the driver it may return some data as a result
     * to be retrieved in the request cycle.
     *
     * @param T $subject
     * @return mixed[]|string|void
     */
    public function authenticate(object $subject);

    /**
     * Checks if the subject is authenticated.
     */
    public function check(): bool;

    /**
     * Retrieves the authenticated subject.
     * @return T|null
     */
    public function subject(): ?object;

    /**
     * Revokes the subject authentication.
     */
    public function revoke(): void;
}

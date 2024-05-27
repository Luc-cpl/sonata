<?php

namespace Sonata\Interfaces;

interface AuthInterface
{
	/**
	 * Performs the authentication process for the given subject.
	 * Depending on the driver it may return some data as a result
	 * to be retrieved in the request cycle.
	 *
	 * @return array|string|void
	 */
	public function authenticate(object $subject);

	/**
	 * Checks if the subject is authenticated.
	 */
	public function check(): bool;

	/**
	 * Retrieves the authenticated subject.
	 */
	public function subject(): ?object;

	/**
	 * Revokes the subject authentication.
	 */
	public function revoke(): void;
}
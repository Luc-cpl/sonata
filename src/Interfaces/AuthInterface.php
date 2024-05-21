<?php

namespace Sonata\Interfaces;

use Sonata\Entities\Abstracts\AbstractUser;

interface AuthInterface
{
	/**
	 * Performs the authentication process for the given user.
	 * Depending on the driver it may return some data as a result
	 * to be retrieved in the request cycle.
	 *
	 * @return array|string|void
	 */
	public function authenticate(AbstractUser $user);

	/**
	 * Checks if the user is authenticated.
	 */
	public function check(): bool;

	/**
	 * Retrieves the authenticated user.
	 */
	public function user(): ?AbstractUser;

	/**
	 * Logs the user out.
	 */
	public function logout(): void;
}
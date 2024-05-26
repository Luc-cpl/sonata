<?php

namespace Sonata\Interfaces;

interface AuthDriverInterface extends AuthInterface
{
	/**
	 * Sets the guard key for the driver.
	 */
	public function setGuard(string $guard): void;

	/**
	 * Sets the user repository to be used by the driver.
	 */
	public function setRepository(RepositoryInterface $repository): void;
}
<?php

namespace Sonata\Interfaces;

interface UserRepositoryInterface extends RepositoryInterface
{
	/**
	 * Filters the users by their email.
	 */
	public function whereEmail(string|array $email): self;
}

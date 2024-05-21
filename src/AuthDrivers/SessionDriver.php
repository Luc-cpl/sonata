<?php

namespace Sonata\AuthDrivers;

use Sonata\Interfaces\AuthDriverInterface;
use Sonata\Interfaces\UserRepositoryInterface;
use Sonata\Entities\Abstracts\AbstractUser;
use Sonata\Interfaces\SessionInterface;

class SessionDriver implements AuthDriverInterface
{
	protected UserRepositoryInterface $repository;

	protected string $guard;

	protected ?AbstractUser $user = null;

	public function __construct(
		protected SessionInterface $session
	) {
		//
	}

	public function setGuard(string $guard): void
	{
		$this->guard = $guard;
	}

	public function setRepository(UserRepositoryInterface $repository): void
	{
		$this->repository = $repository;
	}

	public function authenticate(AbstractUser $user)
	{
		$this->user = $user;
		$this->session->set($this->guardKey(), $user->id);
	}

	public function check(): bool
	{
		/**
		 * As in most cases we will use the user() method in the request cycle,
		 * we can just check if the user() method returns null or not instead of
		 * checking the database for the user existence.
		 */
		return $this->user() !== null;
	}

	public function user(): ?AbstractUser
	{
		if (!$this->session->has($this->guardKey())) {
			return null;
		}
		$this->user ??= $this->repository->whereId($this->session->get($this->guardKey()))->first();
		return $this->user;
	}

	public function logout(): void
	{
		$this->session->remove($this->guardKey());
	}

	protected function guardKey(): string
	{
		return "guards.{$this->guard}.user";
	}
}
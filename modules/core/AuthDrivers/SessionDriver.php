<?php

namespace Sonata\AuthDrivers;

use Sonata\Interfaces\AuthDriverInterface;
use Sonata\Interfaces\RepositoryInterface;
use Sonata\Interfaces\SessionInterface;

class SessionDriver implements AuthDriverInterface
{
	protected RepositoryInterface $repository;

	protected string $guard;

	protected ?object $subject = null;

	public function __construct(
		protected SessionInterface $session
	) {
		//
	}

	public function setGuard(string $guard): void
	{
		$this->guard = $guard;
	}

	public function setRepository(RepositoryInterface $repository): void
	{
		$this->repository = $repository;
	}

	public function authenticate(object $subject)
	{
		$this->subject = $subject;
		$this->session->set($this->guardKey(), $subject->id);
	}

	public function check(): bool
	{
		/**
		 * As in most cases we will use the subject() method in the request cycle,
		 * we can just check if the subject() method returns null or not instead of
		 * checking the database for the subject existence.
		 */
		return $this->subject() !== null;
	}

	public function subject(): ?object
	{
		if (!$this->session->has($this->guardKey())) {
			return null;
		}
		$this->subject ??= $this->repository->whereId($this->session->get($this->guardKey()))->first();
		return $this->subject;
	}

	public function revoke(): void
	{
		$this->session->remove($this->guardKey());
	}

	protected function guardKey(): string
	{
		return "guards.{$this->guard}.subject";
	}
}
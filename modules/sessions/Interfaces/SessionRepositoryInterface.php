<?php

namespace Sonata\Sessions\Interfaces;

use DateTime;
use Sonata\Repositories\Interfaces\Partials\CreatableRepositoryInterface;
use Sonata\Repositories\Interfaces\Partials\DeletableRepositoryInterface;
use Sonata\Repositories\Interfaces\Partials\IdentifiableRepositoryInterface;
use Sonata\Repositories\Interfaces\Partials\IterableRepositoryInterface;
use Sonata\Sessions\Entities\Session;

/**
 * @template T of Session
 * @extends IdentifiableRepositoryInterface<T>
 * @extends CreatableRepositoryInterface<T>
 * @extends DeletableRepositoryInterface<T>
 * @extends IterableRepositoryInterface<T>
 */
interface SessionRepositoryInterface extends
	IdentifiableRepositoryInterface,
	CreatableRepositoryInterface,
	DeletableRepositoryInterface,
	IterableRepositoryInterface
{
	/**
	 * Filter the repository to only include sessions
	 * were last updated happened before the given date.
	 *
	 * @return $this
	 */
	public function updatedBefore(DateTime $date): self;

	/**
	 * Filter the repository to only include sessions
	 * with the given driver name.
	 *
	 * @return $this
	 */
	public function whereDriver(string $driver): self;

	/**
	 * Filter the repository to only include sessions
	 * with the given user ID.
	 *
	 * @return $this
	 */
	public function whereUserId(int|string $userId): self;
}
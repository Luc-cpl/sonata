<?php

namespace Sonata\Interfaces;

use Countable;
use IteratorAggregate;

/**
 * A base interface for repositories.
 */
interface RepositoryInterface extends IteratorAggregate, Countable
{
	public function first(): ?object;

	/**
	 * @param int|string|int[]|string[] $id
	 */
	public function whereId(int|string|array $id): self;

	/**
	 * @return $this
	 */
	public function slice(int $offset, int $limit): self;

	public function persist(object $entity): void;

	public function remove(object $entity): void;
}

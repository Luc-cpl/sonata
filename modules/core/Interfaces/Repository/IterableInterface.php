<?php

namespace Sonata\Interfaces\Repository;

use Countable;
use IteratorAggregate;
use Traversable;

/**
 * A base interface for repositories that can be iterated.
 *
 * @template TCollection of Traversable
 * @template TEntity of object
 * @extends IteratorAggregate<TEntity>
 */
interface IterableInterface extends Countable, IteratorAggregate
{
    /**
     * @return TCollection<array-key, TEntity>
     */
    public function getIterator(): Traversable;

    /**
     * @return TEntity|null
     */
    public function first(): ?object;

    public function exists(): bool;

    /**
     * @return $this
     */
    public function slice(int $offset, int $limit): self;
}

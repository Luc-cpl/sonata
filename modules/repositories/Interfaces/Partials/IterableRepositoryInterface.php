<?php

namespace Sonata\Repositories\Interfaces\Partials;

use Doctrine\Common\Collections\Collection;
use Countable;

/**
 * A base interface for repositories that can be iterated.
 *
 * @template TEntity of object
 */
interface IterableRepositoryInterface extends Countable
{
    /**
     * @return Collection<array-key, TEntity>
     */
    public function getIterator(): Collection;

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

<?php

namespace Tests;

use Sonata\Interfaces\RepositoryInterface;
use ArrayIterator;
use Traversable;

/**
 * A simple dummy repository implementation to be used in the tests.
 */
class TestRepository implements RepositoryInterface
{
    public function __construct(
        private array $data = []
    ) {
        //
    }

    public function first(): ?object
    {
        return !empty($this->data) ? array_values($this->data)[0] : null;
    }

    public function whereId(int|string|array $id): self
    {
        $clone = clone $this;
        $clone->data = array_filter($this->data, fn ($entity) => $entity->id === $id);
        return $clone;
    }

    public function slice(int $offset, int $limit): self
    {
        $clone = clone $this;
        $clone->data = array_slice($this->data, $offset, $limit);
        return $clone;
    }

    public function persist(object $entity): void
    {
        $this->data[$entity->id] = $entity;
    }

    public function remove(object $entity): void
    {
        unset($this->data[$entity->id]);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }
}

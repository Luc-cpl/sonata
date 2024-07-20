<?php

namespace Tests;

use Sonata\Repositories\Interfaces\RepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ArrayIterator;

/**
 * A simple dummy repository implementation to be used in the tests.
 *
 * @implements RepositoryInterface<ArrayIterator, object, array{id:string,mixed}>
 */
class TestRepository implements RepositoryInterface
{
    /**
     * @param object[] $data
     */
    public function __construct(
        private array $data = []
    ) {
        //
    }

    public function first(): ?object
    {
        return !empty($this->data) ? array_values($this->data)[0] : null;
    }

    public function get(int|string $id): ?object
    {
        return $this->data[$id] ?? null;
    }

    public function whereId(int|string|array $id): self
    {
        $clone = clone $this;
        // @phpstan-ignore-next-line
        $clone->data = array_filter($this->data, fn($entity) => in_array($entity->id, (array) $id));
        return $clone;
    }

    public function slice(int $offset, int $limit): self
    {
        $clone = clone $this;
        $clone->data = array_slice($this->data, $offset, $limit);
        return $clone;
    }

    public function make(array $data): object
    {
        return (object) $data;
    }

    public function persist(object $entity): void
    {
        // @phpstan-ignore-next-line
        $this->data[$entity->id] = $entity;
    }

    public function delete(object $entity = null): void
    {
        if ($entity === null) {
            $this->data = [];
            return;
        }
        // @phpstan-ignore-next-line
        unset($this->data[$entity->id]);
    }

    public function getIterator(): Collection
    {
        return new ArrayCollection($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function exists(): bool
    {
        return !empty($this->data);
    }
}

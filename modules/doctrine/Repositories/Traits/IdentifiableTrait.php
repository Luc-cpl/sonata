<?php

namespace Sonata\Doctrine\Repositories\Traits;

trait IdentifiableTrait
{
    public function get(int|string $id): ?object
    {
        return $this->repository->find($id);
    }

    public function whereId(int|string|array $id): self
    {
        $clone = clone $this;
        $queryPart = is_array($id)
            ? "this.$this->idColumn IN (:$this->idColumn)"
            : "this.$this->idColumn = :$this->idColumn";
        $clone->builder->andWhere($queryPart)->setParameter($this->idColumn, $id);
        return $clone;
    }
}

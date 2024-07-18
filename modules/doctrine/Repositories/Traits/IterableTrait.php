<?php

namespace Sonata\Doctrine\Repositories\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Traversable;

trait IterableTrait
{
    public function getIterator(): Traversable
    {
        $result = $this->builder->getQuery()->getResult();
        // @phpstan-ignore-next-line
        return new ArrayCollection($result);
    }

    public function first(): ?object
    {
        // @phpstan-ignore-next-line
        return $this->slice(0, 1)->builder->getQuery()->getOneOrNullResult();
    }

    public function count(): int
    {
        $clone = clone $this;
        if ($this->builder->getMaxResults()) {
            $clone->builder->select("this.$this->idColumn");
            /** @var mixed[] */
            $results = $clone->builder->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
            return count($results);
        }
        $clone->builder->select('count(this)');
        /** @var int */
        return $clone->builder->getQuery()->getSingleScalarResult();
    }

    public function exists(): bool
    {
        return $this->slice(0, 1)->count() > 0;
    }

    public function slice(int $offset, int $limit): self
    {
        $clone = clone $this;
        $clone->builder->setFirstResult($offset)->setMaxResults($limit);
        return $clone;
    }
}

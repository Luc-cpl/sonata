<?php

namespace Sonata\Doctrine\Repositories\Traits;

trait IdentifiableTrait
{
    public function get(int|string $id): ?object
    {
        return $this->repository->find($id);
    }
}

<?php

namespace Sonata\Doctrine\Repositories\Traits;

use Doctrine\ORM\QueryBuilder;

trait DeletableTrait
{
    protected QueryBuilder $builder;

    public function delete(object $object = null): void
    {
        if ($object === null) {
            $this->builder->delete()->getQuery()->execute();
            return;
        }
        $this->manager->remove($object);
    }
}

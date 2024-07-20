<?php

namespace Sonata\Doctrine\Repositories;

use Sonata\Doctrine\Repositories\Traits\CreatableTrait;
use Sonata\Doctrine\Repositories\Traits\DeletableTrait;
use Sonata\Doctrine\Repositories\Traits\IdentifiableTrait;
use Sonata\Doctrine\Repositories\Traits\IterableTrait;
use Sonata\Repositories\Interfaces\RepositoryInterface;
use Doctrine\Common\Collections\Collection;

/**
 * @template TEntity of object
 * @template TData of array
 * @extends AbstractBaseRepository<TEntity>
 * @implements RepositoryInterface<TEntity, TData>
 */
abstract class AbstractRepository extends AbstractBaseRepository implements RepositoryInterface
{
    use CreatableTrait;
    use DeletableTrait;
    use IdentifiableTrait;
    use IterableTrait;
}

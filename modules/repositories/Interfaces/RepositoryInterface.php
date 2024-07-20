<?php

namespace Sonata\Repositories\Interfaces;

use Sonata\Repositories\Interfaces\Partials\CreatableRepositoryInterface;
use Sonata\Repositories\Interfaces\Partials\DeletableRepositoryInterface;
use Sonata\Repositories\Interfaces\Partials\IdentifiableRepositoryInterface;
use Sonata\Repositories\Interfaces\Partials\IterableRepositoryInterface;
use Traversable;

/**
 * A base interface for repositories.
 *
 * @template TEntity of object
 * @template TData of array
 * @extends CreatableRepositoryInterface<TEntity, TData>
 * @extends DeletableRepositoryInterface<TEntity>
 * @extends IdentifiableRepositoryInterface<TEntity>
 * @extends IterableRepositoryInterface<TEntity>
 */
interface RepositoryInterface extends
    CreatableRepositoryInterface,
    DeletableRepositoryInterface,
    IdentifiableRepositoryInterface,
    IterableRepositoryInterface
{
    //
}

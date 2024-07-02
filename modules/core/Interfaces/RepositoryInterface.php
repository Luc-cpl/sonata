<?php

namespace Sonata\Interfaces;

use Sonata\Interfaces\Repository\CreatableRepositoryInterface;
use Sonata\Interfaces\Repository\DeletableRepositoryInterface;
use Sonata\Interfaces\Repository\IdentifiableRepositoryInterface;
use Sonata\Interfaces\Repository\IterableRepositoryInterface;
use Traversable;

/**
 * A base interface for repositories.
 *
 * @template TCollection of Traversable
 * @template TEntity of object
 * @template TData of array
 * @extends CreatableRepositoryInterface<TEntity, TData>
 * @extends DeletableRepositoryInterface<TEntity>
 * @extends IdentifiableRepositoryInterface<TEntity>
 * @extends IterableRepositoryInterface<TCollection, TEntity>
 */
interface RepositoryInterface extends
    CreatableRepositoryInterface,
    DeletableRepositoryInterface,
    IdentifiableRepositoryInterface,
    IterableRepositoryInterface
{
    //
}

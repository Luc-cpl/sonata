<?php

namespace Sonata\Interfaces;

use Sonata\Interfaces\Repository\CreatableInterface;
use Sonata\Interfaces\Repository\DeletableInterface;
use Sonata\Interfaces\Repository\IdentifiableInterface;
use Sonata\Interfaces\Repository\IterableInterface;
use Traversable;

/**
 * A base interface for repositories.
 *
 * @template TCollection of Traversable
 * @template TEntity of object
 * @template TData of array
 * @extends CreatableInterface<TEntity, TData>
 * @extends DeletableInterface<TEntity>
 * @extends IdentifiableInterface<TEntity>
 * @extends IterableInterface<TCollection, TEntity>
 */
interface RepositoryInterface extends
    CreatableInterface,
    DeletableInterface,
    IdentifiableInterface,
    IterableInterface
{
    //
}

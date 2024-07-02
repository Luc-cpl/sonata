<?php

namespace Sonata\Interfaces\Repository;

/**
 * @template TEntity of object
 * @template TData of array
 */
interface CreatableRepositoryInterface
{
    /**
     * @param TData $data
     * @return TEntity
     */
    public function make(array $data): object;

    /**
     * @param TEntity $object
     */
    public function persist(object $object): void;
}

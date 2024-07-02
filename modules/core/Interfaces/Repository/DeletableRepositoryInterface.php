<?php

namespace Sonata\Interfaces\Repository;

/**
 * @template T of object
 */
interface DeletableRepositoryInterface
{
    /**
     * @param T $object
     */
    public function delete(object $object): void;
}

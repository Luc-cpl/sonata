<?php

namespace Sonata\Interfaces\Repository;

/**
 * @template T of object
 */
interface DeletableInterface
{
    /**
     * @param T $object
     */
    public function delete(object $object): void;
}
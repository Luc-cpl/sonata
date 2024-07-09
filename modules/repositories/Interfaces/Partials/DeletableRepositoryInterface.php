<?php

namespace Sonata\Repositories\Interfaces\Partials;

/**
 * @template T of object
 */
interface DeletableRepositoryInterface
{
    /**
     * @param ?T $object
     */
    public function delete(?object $object = null): void;
}

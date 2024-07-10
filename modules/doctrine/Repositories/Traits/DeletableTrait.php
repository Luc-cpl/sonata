<?php

namespace Sonata\Doctrine\Repositories\Traits;

use Traversable;

trait DeletableTrait
{
    abstract public function getIterator(): Traversable;

    public function delete(object $object): void
    {
        $this->manager->remove($object);
    }
}

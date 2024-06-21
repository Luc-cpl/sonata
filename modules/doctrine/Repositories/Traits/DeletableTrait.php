<?php

namespace Sonata\Doctrine\Repositories\Traits;

trait DeletableTrait
{
    public function delete(object $object): void
    {
        $this->manager->remove($object);
    }
}

<?php

namespace Sonata\Doctrine\Repositories\Traits;

trait CreatableTrait
{
    public function make(array $data): object
    {
        return $this->factory->make($this->entityClass, $data);
    }

    public function persist(object $entity): void
    {
        $this->manager->persist($entity);
    }
}

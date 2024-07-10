<?php

namespace Sonata\Doctrine\Repositories\Traits;

use Orkestra\Entities\EntityFactory;

trait CreatableTrait
{
    protected EntityFactory $factory;

    public function make(array $data): object
    {
        // @phpstan-ignore-next-line
        return $this->factory->make($this->entityClass, ...$data);
    }

    public function persist(object $entity): void
    {
        $this->manager->persist($entity);
    }
}

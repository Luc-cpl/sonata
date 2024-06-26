<?php

namespace Sonata\Interfaces\Repository;

/**
 * @template T of object
 */
interface IdentifiableInterface
{
    /**
     * @return T|null
     */
    public function get(int|string $id): ?object;

    /**
     * @param int|string|(string|int)[] $id
     * @return $this
     */
    public function whereId(int|string|array $id): self;
}

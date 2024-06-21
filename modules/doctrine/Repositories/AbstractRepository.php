<?php

namespace Sonata\Doctrine\Repositories;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Orkestra\Entities\EntityFactory;
use Sonata\Interfaces\RepositoryInterface;
use Traversable;

/**
 * @template TEntity of object
 * @template TData of array
 * @implements RepositoryInterface<Collection, TEntity, TData>
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var EntityRepository<TEntity>
     */
    protected EntityRepository $repository;

    protected QueryBuilder $builder;

    protected string $idColumn = 'id';

    /**
     * @param class-string<TEntity> $entityClass
     */
    public function __construct(
        protected EntityFactory $factory,
        protected EntityManagerInterface $manager,
        protected string $entityClass,
    ) {
        $this->repository = $manager->getRepository($entityClass);
        $this->builder    = $this->repository->createQueryBuilder('this');
    }

    public function getIterator(): Traversable
    {
        /** @var TEntity[] */
        $result = $this->builder->getQuery()->getResult();
        return new ArrayCollection($result);
    }

    public function first(): ?object
    {
        /** @var TEntity|null */
        return $this->builder->getQuery()->getOneOrNullResult();
    }

    public function count(): int
    {
        $clone = clone $this;
        if ($this->builder->getMaxResults()) {
            $clone->builder->select("this.$this->idColumn");
            /** @var mixed[] */
            $results = $clone->builder->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
            return count($results);
        }
        $clone->builder->select('count(this)');
        /** @var int */
        return $clone->builder->getQuery()->getSingleScalarResult();
    }

    public function exists(): bool
    {
        return $this->slice(0, 1)->count() > 0;
    }

    public function get(int|string $id): ?object
    {
        return $this->repository->find($id);
    }

    public function slice(int $offset, int $limit): self
    {
        $clone = clone $this;
        $clone->builder->setFirstResult($offset)->setMaxResults($limit);
        return $clone;
    }

    public function make(array $data): object
    {
        return $this->factory->make($this->entityClass, $data);
    }

    public function persist(object $entity): void
    {
        $this->manager->persist($entity);
    }

    public function delete(object $entity): void
    {
        $this->manager->remove($entity);
    }

    public function __clone(): void
    {
        $this->repository = clone $this->repository;
        $this->builder = clone $this->builder;
    }
}

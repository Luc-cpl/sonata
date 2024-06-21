<?php

namespace Sonata\Doctrine\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Orkestra\Entities\EntityFactory;

/**
 * @template TEntity of object
 */
abstract class AbstractBaseRepository
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

    public function __clone(): void
    {
        $this->repository = clone $this->repository;
        $this->builder = clone $this->builder;
    }
}

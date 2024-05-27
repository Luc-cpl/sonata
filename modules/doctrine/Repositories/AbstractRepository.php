<?php

namespace Sonata\Doctrine\Repositories;

use Sonata\Interfaces\RepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Traversable;

abstract class AbstractRepository implements RepositoryInterface
{
	protected EntityRepository $repository;
	protected QueryBuilder $builder;

	protected string $idColumn = 'id';

	public function __construct(
		protected EntityManagerInterface $manager,
		string $entityClass,
	) {
		$this->repository = $manager->getRepository($entityClass);
		$this->builder    = $this->repository->createQueryBuilder('this');
	}

	public function getIterator(): Traversable
	{
		return new ArrayCollection($this->builder->getQuery()->getResult());
	}

	public function first(): ?object
	{
		return $this->builder->getQuery()->getOneOrNullResult();
	}

	public function count(): int
	{
		$clone = clone $this;
		if ($this->builder->getMaxResults()) {
			$clone->builder->select("this.$this->idColumn");
			return count($clone->builder->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY));
		}
		$clone->builder->select('count(this)');
		return $clone->builder->getQuery()->getSingleScalarResult();
	}

	public function whereId(int|string|array $id): self
	{
		$clone = clone $this;
		$queryPart = is_array($id)
			? "this.$this->idColumn IN (:$this->idColumn)"
			: "this.$this->idColumn = :$this->idColumn";
		$clone->builder->andWhere($queryPart)->setParameter($this->idColumn, $id);
		return $clone;
	}

	public function slice(int $offset, int $limit): self
	{
		$clone = clone $this;
		$clone->builder->setFirstResult($offset)->setMaxResults($limit);
		return $clone;
	}

	public function persist(object $entity): void
	{
		$this->manager->persist($entity);
	}

	public function remove(object $entity): void
	{
		$this->manager->remove($entity);	
	}

	public function __clone(): void
	{
		$this->repository = clone $this->repository;
		$this->builder = clone $this->builder;
	}
}
<?php

namespace Sonata\Doctrine\Repositories;

use Doctrine\Common\Collections\ArrayCollection;
use Sonata\Interfaces\UserRepositoryInterface;
use Sonata\Doctrine\Entities\Abstracts\AbstractUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Traversable;

class UserRepository implements UserRepositoryInterface
{
	protected EntityRepository $repository;
	protected QueryBuilder $builder;

	public function __construct(
		protected EntityManagerInterface $manager,
		string $userClass,
	) {
		$this->repository = $manager->getRepository($userClass);
		$this->builder    = $this->repository->createQueryBuilder('u');
	}

	public function getIterator(): Traversable
	{
		return new ArrayCollection($this->builder->getQuery()->getResult());
	}

	public function first(): ?AbstractUser
	{
		return $this->builder->getQuery()->getOneOrNullResult();
	}

	public function count(): int
	{
		$clone = clone $this;
		if ($this->builder->getMaxResults()) {
			$clone->builder->select('u.id');
			return count($clone->builder->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY));
		}
		$clone->builder->select('count(u.id)');
		return $clone->builder->getQuery()->getSingleScalarResult();
	}

	public function whereId(int|string|array $id): self
	{
		$clone = clone $this;
		$queryPart = is_array($id) ? 'u.id IN (:id)' : 'u.id = :id';
		$clone->builder->andWhere($queryPart)->setParameter('id', $id);
		return $clone;
	}

	public function whereEmail(string|array $email): self
	{
		$clone = clone $this;
		$queryPart = is_array($email) ? 'u.email IN (:email)' : 'u.email = :email';
		$clone->builder->andWhere($queryPart)->setParameter('email', $email);
		return $clone;
	}

	public function slice(int $offset, int $limit): self
	{
		$clone = clone $this;
		$clone->builder->setFirstResult($offset)->setMaxResults($limit);
		return $clone;
	}

	public function add(AbstractUser $user): void
	{
		$this->manager->persist($user);
	}

	public function remove(AbstractUser $user): void
	{
		$this->manager->remove($user);	
	}

	public function __clone(): void
	{
		$this->repository = clone $this->repository;
		$this->builder = clone $this->builder;
	}
}
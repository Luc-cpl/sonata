<?php

namespace Sonata\Sessions\Repositories;

use Sonata\Doctrine\Repositories\AbstractRepository;
use Sonata\Sessions\Interfaces\SessionRepositoryInterface;
use Sonata\Sessions\Entities\Session;
use DateTime;

class DoctrineSessionRepository extends AbstractRepository implements SessionRepositoryInterface
{
	protected string $entityClass = Session::class;

	public function delete(object $object): void
	{
		parent::delete($object);
		$this->manager->flush();
	}

	public function persist(object $session): void
	{
		parent::persist($session);
		$this->manager->flush();
	}

	public function updatedBefore(DateTime $date): self
	{
		$clone = clone $this;
		$clone->builder->andWhere('this.updatedAt < :date')->setParameter('date', $date);

		return $clone;
	}

	public function whereDriver(string $driver): self
	{
		$clone = clone $this;
		$clone->builder->andWhere('this.driver = :driver')->setParameter('driver', $driver);
		return $clone;
	}

	public function whereUserId(int|string $userId): self
	{
		$clone = clone $this;
		$clone->builder->andWhere('this.userId = :userId')->setParameter('userId', $userId);
		return $clone;
	}
}
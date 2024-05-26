<?php

namespace Sonata\Doctrine\Repositories;

use Sonata\Interfaces\UserRepositoryInterface;

class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
	public function whereEmail(string|array $email): self
	{
		$clone = clone $this;
		$queryPart = is_array($email) ? 'this.email IN (:email)' : 'this.email = :email';
		$clone->builder->andWhere($queryPart)->setParameter('email', $email);
		return $clone;
	}
}
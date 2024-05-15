<?php

namespace Sonata\Repositories\Interfaces;

use Doctrine\Common\Collections\Collection;
use Sonata\Entities\Abstracts\AbstractUser;

interface UserRepositoryInterface
{
	/**
	 * @return Collection<AbstractUser>
	 */
	public function all(): Collection;

	/**
	 * @return ?AbstractUser
	 */
	public function first(): ?object;

	public function count(): int;

	/**
	 * @param int|string|int[]|string[] $id
	 */
	public function whereId(int|string|array $id): self;

	/**
	 * @param string|string[] $email
	 */
	public function whereEmail(string|array $email): self;

	public function search(string $search): self;

	public function slice(int $offset, int $limit): self;

	public function add(AbstractUser $user): void;

	public function remove(AbstractUser $user): void;
}
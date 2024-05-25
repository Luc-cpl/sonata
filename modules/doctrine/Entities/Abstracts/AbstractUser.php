<?php

namespace Sonata\Doctrine\Entities\Abstracts;

use Orkestra\Entities\AbstractEntity;
use Orkestra\Entities\Attributes\Faker;
use Doctrine\ORM\Mapping as Doctrine;
use InvalidArgumentException;

/**
 * @property-read string $email
 * @property-read int|string|null $id
 */
abstract class AbstractUser extends AbstractEntity
{
	#[Doctrine\Id]
	#[Doctrine\GeneratedValue(strategy: 'AUTO')]
	#[Doctrine\Column(type: 'integer')]
	protected int|string|null $id;

	#[Faker(method: 'email')]
	#[Doctrine\Column(type: 'string', length: 255, unique: true)]
	protected string $email;

	#[Faker(method: 'password')]
	#[Doctrine\Column(type: 'string', length: 255)]
	protected string $password;

	public function getEmail(): string
	{
		return $this->email;
	}

	public function setEmail(string $email): void
	{
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new InvalidArgumentException('Invalid email address');
		}
		$this->email = $email;
	}

	public function setPassword(string $password): void
	{
		$this->password = password_hash($password, PASSWORD_DEFAULT);
	}

	public function verifyPassword(string $password): bool
	{
		$verified = password_verify($password, $this->password);

		if (!$verified) {
			return false;
		}

		if (password_needs_rehash($this->password, PASSWORD_DEFAULT)) {
			$this->set(password: $password);
		}

		return true;
	}
}
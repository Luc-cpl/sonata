<?php

namespace Sonata\Sessions\Entities;

use Orkestra\Entities\AbstractEntity;
use Doctrine\ORM\Mapping as Doctrine;
use Orkestra\Entities\Attributes\Faker;
use DateTime;
use Orkestra\Entities\Attributes\Repository;
use Sonata\Sessions\Repositories\DoctrineSessionRepository;

/**
 * @property-read int|string $id
 * @property-read string $data
 * @property-read string $driver
 * @property-read DateTime $updatedAt
 * @property-read ?string $ip
 * @property-read ?string $userAgent
 * @property-read int|string|null $userId
 */
 #[Doctrine\Entity]
 #[Doctrine\Table(name: 'sessions')]
 #[Repository(DoctrineSessionRepository::class)]
class Session extends AbstractEntity
{
	public function __construct(
		#[Doctrine\Id]
		#[Doctrine\GeneratedValue(strategy: 'NONE')]
		#[Doctrine\Column(type: 'string')]
		#[Faker(method: 'uuid')]
		protected string $id,

		#[Doctrine\Column(type: 'string', length: 50)]
		#[Faker(value: 'doctrine')]
		protected string $driver = '',

		#[Doctrine\Column(type: 'text')]
		#[Faker(value: '')]
		protected string $data = '',

		#[Doctrine\Column(type: 'string', length: 15)]
		#[Faker(method: 'ipv4')]
		protected ?string $ip = null,

		#[Doctrine\Column(type: 'string', length: 255)]
		#[Faker(method: 'userAgent')]
		protected ?string $userAgent = null,

		#[Doctrine\Column(type: 'string', nullable: true)]
		protected string|int|null $userId = null,

		#[Doctrine\Column(type: 'datetime')]
		#[Faker(method: 'dateTimeThisYear')]
		protected ?DateTime $updatedAt = null,
	) {
		//
	}

	public function getId(): int|string
	{
		return is_numeric($this->id) ? (int) $this->id : $this->id;
	}
}
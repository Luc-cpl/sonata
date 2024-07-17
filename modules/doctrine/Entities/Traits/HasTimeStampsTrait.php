<?php

namespace Sonata\Doctrine\Entities\Traits;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

/**
 * Trait to add created_at and updated_at fields to an entity.
 * Please, add the `Doctrine\ORM\Mapping\HasLifecycleCallbacks`
 * attribute to the entity class to make this trait work.
 */
trait HasTimeStampsTrait
{
	#[Column(name: 'created_at', type: 'datetime')]
	protected \DateTimeInterface $createdAt;

	#[Column(name: 'updated_at', type: 'datetime')]
	protected \DateTimeInterface $updatedAt;

	#[PrePersist]
	public function setCreatedAtValue(): void
	{
		$this->$createdAt = new \DateTimeImmutable();
		$this->setUpdatedAtValue();
	}

	#[PreUpdate]
	public function setUpdatedAtValue(): void
	{
		$this->$updatedAt = new \DateTimeImmutable();
	}
}

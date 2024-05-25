<?php

namespace Sonata\Doctrine\Entities\Partials;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;

trait HasUpdatedAt
{
	#[ORM\Column(type: 'datetime')]
	protected DateTimeInterface $updatedAt;

	#[ORM\PreUpdate]
	#[ORM\PrePersist]
	public function setUpdatedAtValue(): void
	{
		$this->updatedAt = new DateTime();
	}
}
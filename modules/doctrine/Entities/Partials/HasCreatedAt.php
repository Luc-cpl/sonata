<?php

namespace Sonata\Doctrine\Entities\Partials;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;

trait HasCreatedAt
{
	#[ORM\Column(type: 'datetime')]
	protected DateTimeInterface $createdAt;

	#[ORM\PrePersist]
	public function setCreatedAtValue(): void
	{
		$this->createdAt = new DateTime();
	}
}
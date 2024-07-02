<?php

namespace Sonata\Interfaces\Entity;

interface IdentifiableInterface
{
	public function getId(): int|string|null;
}
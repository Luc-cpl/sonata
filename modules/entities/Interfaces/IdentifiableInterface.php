<?php

namespace Sonata\Entities\Interfaces;

interface IdentifiableInterface
{
	public function getId(): int|string|null;
}
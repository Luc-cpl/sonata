<?php

namespace Sonata\Sessions\Interfaces;

interface SessionInterface
{
	public function id(): string|false;

	public function get(string $key): mixed;

	public function set(string $key, mixed $value): void;

	public function remove(string $key): void;

	public function has(string $key): bool;

	public function clear(): void;

	public function commit(): void;
}

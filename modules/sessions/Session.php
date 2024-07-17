<?php

namespace Sonata\Sessions;

use Sonata\Sessions\Interfaces\SessionInterface;

class Session implements SessionInterface
{
	public function id(): string
	{
		return session_id();
	}

	public function get(string $key): mixed
	{
		return $_SESSION[$key] ?? null;
	}

	public function set(string $key, mixed $value): void
	{
		$_SESSION[$key] = $value;
	}

	public function remove(string $key): void
	{
		unset($_SESSION[$key]);
	}

	public function has(string $key): bool
	{
		return isset($_SESSION[$key]);
	}

	public function clear(): void
	{
		session_unset();
		unset($_SESSION);
	}

	public function commit(): void
	{
		session_write_close();
	}
}